<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Category;
use AppBundle\Entity\Map;
use AppBundle\Entity\MapImage;
use AppBundle\Entity\Post;
use AppBundle\Entity\Tag;
use AppBundle\Entity\User;
use AppBundle\Form\Type\CreateMapType;
use AppBundle\Form\Type\CreatePostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminController extends BaseController
{
    /**
     * @Route("/", name="admin")
     */
    public function adminController()
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/maps/{$page}", name="admin_maps", defaults={"page" = 1}, requirements={"page" = "\d+"})
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mapListController($page)
    {
        $paginator = $this->getAllPagination($page, Map::class);
        return $this->render('admin/mapsList.html.twig', [
            'paginator' => $paginator
        ]);
    }
    /**
     * @Route("/posts/{page}", name="admin_posts", defaults={"page" = 1}, requirements={"page" = "\d+"})
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postListController($page)
    {
        $paginator = $this->getQueryPagination([
            'status' => 'published',
            'order' => 'DESC'
        ], $page, Post::class);
        return $this->render('admin/postList.html.twig', [
            'paginator' => $paginator
        ]);
    }

    /**
     * @Route("/map/edit/{id}", name="admin_map_edit")
     * @ParamConverter("map", class="AppBundle\Entity\Map", options={"mapping": {"id": "id"}})
     * @param $map
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function mapEditController($map, Request $request)
    {
        $form = $this->createForm(CreateMapType::class, $map);
        $form->handleRequest($request);
        if ($form->isSubmitted() & $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();

            /**
             * @var MapImage $image
             */
            foreach ($map->getImage as $image)
            {
                $image->setMap($map);
                $em->persist($image);
            }
            $em->persist($map);
            $em->flush();
            $this->addFlash('success', 'Done!');
            return $this->redirectToRoute('admin_maps');
        }
        return $this->render('admin/editMap.html.twig', [
            'form' => $form->createView(),
            'map' => $map
        ]);
    }

    /**
     * @Route("/map/delete/{id}", name="admin_map_delete")
     * @ParamConverter("map", class="AppBundle\Entity\Map", options={"mapping": {"id": "id"}})
     * @param $map
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteMapController($map)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($map);
        $em->flush();
        $this->addFlash('success', 'You have deleted map');
        return $this->redirectToRoute('admin_maps');
    }

    /**
     * @Route("/post/edit/{slug}", name="admin_post_edit")
     * @ParamConverter("post", class="AppBundle\Entity\Post", options={"mapping": {"slug": "slug"}})
     * @param $post
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postEditController($post, Request $request)
    {
        if (!$post)
        {
            throw $this->createNotFoundException('Post not found');
        }
        $form = $this->createForm(CreatePostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() & $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            $this->addFlash('success', 'Done!');
            return $this->redirectToRoute('admin_posts');
        }

        return $this->render('admin/editPost.html.twig', [
           'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/create", name="admin_post_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postCreateController(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(CreatePostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() & $form->isValid())
        {
            $post->setAuthor($this->getUser());
            $post->setCreateDeate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            $this->addFlash('success', 'Done!');
            return $this->redirectToRoute('admin_posts');
        }

        return $this->render('admin/editPost.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/delete/{slug}", name="admin_post_delete")
     * @ParamConverter("post", class="AppBundle\Entity\Post", options={"mapping": {"slug": "slug"}})
     * @param $post
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postDeleteController($post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        return $this->redirectToRoute('admin_posts');
    }
    /**
     * @Route("/categories/{page}", name="admin_categories", defaults={"page" = 1}, requirements={"page" = "\d+"})
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryListController($page)
    {
        $paginator = $this->getAllPagination($page, Category::class);
        return $this->render('admin/categoriesList.html.twig', [
            'paginator' => $paginator
        ]);
    }
    /**
     * @Route("/category/edit/{id}", name="admin_category_edit")
     */
    public function categoryEditCotroller()
    {

    }
    public function categoryNewController()
    {

    }
    /**
     * @Route("/tags/{page}", name="admin_tags", defaults={"page" = 1}, requirements={"page" = "\d+"})
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tagListController($page)
    {
        $paginator = $this->getAllPagination($page, Tag::class);
        return $this->render('admin/tagsList.html.twig', [
            'paginator' => $paginator
        ]);
    }
    /**
     * @Route("/tag/edit/{id}", name="admin_tag_edit")
     */
    public function tagEditCotroller()
    {

    }
    public function tagNewController()
    {

    }
    /**
     * @Route("/users/{page}", name="admin_users", defaults={"page" = 1}, requirements={"page" = "\d+"})
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersListController($page)
    {
        $paginator = $this->getAllPagination($page, User::class);
        return $this->render('admin/usersList.html.twig', [
            'paginator' => $paginator
        ]);
    }
}