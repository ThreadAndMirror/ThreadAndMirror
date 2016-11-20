<?php

namespace ThreadAndMirror\InstaInspoBundle\Service\Manager;


use Oh\EmojiBundle\Service\EmojiConverter;
use ThreadAndMirror\InstaInspoBundle\Entity\Post;
use ThreadAndMirror\InstaInspoBundle\Repository\PostRepository;
use ThreadAndMirror\SocialBundle\Service\FeedManagerService;

class PostManager
{
    /**
     * @var FeedManagerService
     */
    private $feedManagerService;

    /**
     * @var PostRepository
     */
    private $repository;

    /**
     * @var EmojiConverter
     */
    private $emojiConverter;

    /**
     * @param FeedManagerService $feedManagerService
     */
    public function __construct(
        FeedManagerService $feedManagerService,
        PostRepository $repository,
        EmojiConverter $emojiConverter
    ) {
        // @todo use a more generic api service
        $this->feedManagerService = $feedManagerService;
        $this->repository = $repository;
        $this->emojiConverter = $emojiConverter;
    }

    /**
     * @param string $url
     *
     * @return Post
     */
    public function createPostFromUrl($url)
    {
        $shortcode = explode('/', $url);
        $shortcode = $shortcode[4];

        $data = $this->feedManagerService->getPostByShortcode($shortcode);

        $post = new Post();

        // Convert emoji to html
        $caption = $this->emojiConverter->iPhoneToHtml($data['caption']['text']);

        // Add spaces for tags in captions
        $caption = str_replace('#', ' #', $caption);

        $post->setUrl($url);
        $post->setImage($data['images']['standard_resolution']['url']);
        $post->setCaption($caption);
        $post->setTags($data['tags']);
        $post->setPid($shortcode);

        $this->repository->save($post);

        return $post;
    }
}