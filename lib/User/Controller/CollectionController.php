<?php

namespace Dlx\Security\User\Controller;

use Daikon\Elasticsearch5\Query\Elasticsearch5Query;
use Daikon\ReadModel\Repository\RepositoryMap;
use Dlx\Security\User\View\CollectionSuccessView;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

final class CollectionController
{
    private $repositoryMap;

    public function __construct(RepositoryMap $repositoryMap)
    {
        $this->repositoryMap = $repositoryMap;
    }

    public function read(Request $request, Application $app)
    {
        $query = $request->query->get('q', '');
        $page = (int) $request->query->get('page', 1);
        $size = (int) $request->query->get('size', 10);

        $users = $this->loadUsers($query, $page, $size);
        $request->attributes->set('users', $users);

        return [CollectionSuccessView::CLASS];
    }

    private function loadUsers($query, $page, $size)
    {
        $repository = $this->repositoryMap->get('dlx.security.user.standard');
        $users = $repository->search(new Elasticsearch5Query, 0, 10);

        return (new Pagerfanta(new FixedAdapter($users->count(), $users->toArray())))
            ->setMaxPerPage($size) // call before setCurrentPage()
            ->setCurrentPage($page);
    }
}
