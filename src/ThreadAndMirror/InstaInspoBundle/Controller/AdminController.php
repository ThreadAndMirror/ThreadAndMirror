<?php

namespace ThreadAndMirror\InstaInspoBundle\Controller;

use Stems\CoreBundle\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use ThreadAndMirror\InstaInspoBundle\Entity\Post;
use ThreadAndMirror\InstaInspoBundle\Form\CreatePostType;
use ThreadAndMirror\InstaInspoBundle\Form\EditPostType;

/**
 * @Route("/admin/insta-inspo", name="thread_instainspo_admin")
 */
class AdminController extends BaseAdminController
{
    /**
     * Overview of all magazine posts
     *
     * @Route("/", name="thread_instainspo_admin_index")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $posts = $this->em->getRepository('ThreadAndMirrorInstaInspoBundle:Post')->findAll();

        return [
            'posts' => $posts,
        ];
    }

    /**
     * @Route("/create", name="thread_instainspo_admin_create")
     * @Template()
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        $post = new Post();

        $form = $this->createForm(new CreatePostType(), $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $url = $form->getData()->getUrl();
            $post = $this->get('threadandmirror.insta_inspo.manager.post')->createPostFromUrl($url);

            return $this->redirect($this->generateUrl('thread_instainspo_admin_edit', [
                'id' => $post->getId()
            ]));
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{id}", name="thread_instainspo_admin_edit")
     * @Template()
     *
     * @param Request $request
     * @param Post $post
     *
     * @return array
     */
    public function editAction(Request $request, Post $post)
    {
        $form = $this->createForm(new EditPostType(), $post);

        return [
            'post' => $post,
            'form' => $form->createView()
        ];
    }
}
