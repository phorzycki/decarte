<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProductCollectionRepository extends EntityRepository
{
    use SortableRepositoryTrait;

    public function getProductCollections($typeId, $onlyVisible = true)
    {
        $queryBuilder = $this->createQueryBuilder('pc');
        $queryBuilder
            ->where('pc.productType = :type')
            ->setParameter(':type', $typeId)
            ->orderBy('pc.sort', 'ASC');

        if ($onlyVisible) {
            $queryBuilder->andWhere('pc.isVisible = :visible')->setParameter(':visible', true);
        }

        $query = $queryBuilder->getQuery();
        return $query->getResult();
    }

    public function findBySlugName(string $typeSlugName, string $collectionSlugName, bool $onlyVisible = true)
    {
        $queryBuilder = $this->createQueryBuilder('pc');
        $queryBuilder
            ->join('pc.productType', 'pt')
            ->where('pt.slugName = :type')
            ->andWhere('pc.slugName = :collection')
            ->setParameter(':type', $typeSlugName)
            ->setParameter(':collection', $collectionSlugName)
            ->setMaxResults(1);

        if ($onlyVisible) {
            $queryBuilder
                ->andWhere('pt.isVisible = :visible')
                ->andWhere('pc.isVisible = :visible')
                ->setParameter(':visible', true);
        }

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return is_array($result) ? $result[0] : null;
    }

    protected function getSortGroupField()
    {
        return 'productType';
    }
}
