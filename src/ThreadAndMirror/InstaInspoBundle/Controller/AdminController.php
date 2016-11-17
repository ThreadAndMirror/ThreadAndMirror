<?php

namespace ThreadAndMirror\InstaInspoBundle\Controller;

use Stems\CoreBundle\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ThreadAndMirror\InstaInspoBundle\Entity\Post;
use ThreadAndMirror\InstaInspoBundle\Form\CreatePostType;

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
     */
    public function createAction(Request $request)
    {
        $post = new Post();
        $form = new CreatePostType();

        $post->setAuthor($this->getUser()->getId());
        $category = $this->em->getRepository('ThreadAndMirrorBlogBundle:Category')->find(1);
        $post->setCategory($category);

        $this->em->persist($post);

        // If a title was posted then use it
        $request->get('title') and $post->setTitle($request->get('title'));
        $this->em->flush();

        // Save all the things
        $this->em->flush();

        // Redirect to the edit page for the new post
        return $this->redirect($this->generateUrl('thread_blog_admin_edit', array('id' => $post->getId())));
    }
}
