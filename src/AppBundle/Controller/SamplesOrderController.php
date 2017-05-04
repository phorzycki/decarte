<?php

namespace AppBundle\Controller;

use AppBundle\Entity\SamplesOrder;
use AppBundle\Entity\SamplesOrderItem;
use AppBundle\Form\OrderSamplesType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SamplesOrderController extends Controller
{
    /**
     * @Route("/zamow-probki", name="shop_order_samples")
     * @param Request $request
     * @return Response
     */
    public function orderSamplesAction(Request $request)
    {
        $order = new SamplesOrder();
        for ($n = 0; $n < $this->getParameter('samples_count'); $n++) {
            $order->addItem(new SamplesOrderItem());
        }

        $em = $this->getDoctrine()->getManager();
        $productType = $em->getRepository('AppBundle:ProductType')->find(1);
        $products = $em->getRepository('AppBundle:Product')->findDemos($productType);

        $form = $this->createForm(OrderSamplesType::class, $order, [
            'products' => $products,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();
            $this->sendSamplesOrderEmailToShop($order);
            $this->sendSamplesOrderEmailToCustomer($order);

            return $this->redirectToRoute('shop_order_samples_confirmation');
        }

        return $this->render('shop/orderSamples.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/zamow-probki/potwierdzenie", name="shop_order_samples_confirmation")
     * @return Response
     */
    public function orderSamplesConfirmationAction()
    {
        return $this->render('shop/orderSamplesConfirmation.html.twig');
    }

    protected function sendSamplesOrderEmailToShop(SamplesOrder $order)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('ZAMÓWIENIE WWW - PRÓBKI')
            ->setTo($this->getParameter('admin_mail'))
            ->setFrom([$this->getParameter('admin_mail') => $order->getName()])
            ->setReplyTo($order->getEmail())
            ->setBody(
                $this->renderView('shop/mail/samplesOrderShop.html.twig', [
                    'order' => $order,
                ]),
                'text/html'
            );

        $this->get('mailer')->send($message);
    }

    protected function sendSamplesOrderEmailToCustomer(SamplesOrder $order)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Zamówienie decarte.com.pl - próbki')
            ->setTo($order->getEmail())
            ->setFrom([$this->getParameter('admin_mail') => 'Sklep ślubny decARTe.com.pl'])
            ->setReplyTo($this->getParameter('admin_mail'))
            ->setBody(
                $this->renderView('shop/mail/samplesOrderCustomer.html.twig', [
                    'order' => $order,
                ]),
                'text/html'
            );

        $this->get('mailer')->send($message);
    }
}