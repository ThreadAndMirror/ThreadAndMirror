<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class AsosFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function cleanupFeedName(Product $product)
    {
        $result = $this
            ->format($product->getName())
            ->remove('ASOS CURVE')
            ->remove('ASOS MATERNITY')
            ->remove('ASOS PETITE')
            ->remove('ASOS TALL')
            ->remove('ASOS WHITE')
            ->replace($product->getBrandName() . ' ', '')
            ->sheer(' - ', false)
            ->trim()
            ->result();

        $product->setName($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupFeedDescription(Product $product)
    {
        $result = $this
            ->format($product->getName())
            ->sheer('ABOUT')
            ->append('.')
            ->result();

        $product->setDescription($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupFeedPid(Product $product)
    {
        $result = $this
            ->format($product->getImages()[0])
            ->explode('/', 9)
            ->result();

        $product->setDescription($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupFeedImages(Product $product)
    {
        $product->setImages([$product->getImages()[0]]);
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupFeedPortraits(Product $product)
    {
        $product->setPortraits(array($product->getImages()[0]));
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupFeedThumbnails(Product $product)
    {
        $result = $this
            ->format($product->getImages()[0])
            ->replace('image1xl.jpg', 'image1s.jpg')
            ->result();

        $product->setThumbnails(array($result));
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupCrawledName(Product $product)
    {
        $result = $this
            ->format($product->getName())
            ->remove('ASOS CURVE')
            ->remove('ASOS MATERNITY')
            ->remove('ASOS PETITE')
            ->remove('ASOS TALL')
            ->remove('ASOS WHITE')
            ->remove('ASOS')
            ->replace($product->getBrandName() . ' ', '')
            ->trim()
            ->result();

        $product->setName($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupCrawledDescription(Product $product)
    {
        $result = $this
            ->format($product->getDescription())
            ->implode()
            ->append('.')
            ->result();

        $product->setDescription($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupCrawledNow(Product $product)
    {
        $result = $this
            ->format($product->getNow())
            ->sheer('{"current":')
            ->sheer(',"previous":', false)
            ->result();

        $product->setNow($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupCrawledWas(Product $product)
    {
        $result = $this
            ->format($product->getWas())
            ->sheer(',"previous":')
            ->sheer(',"rrp":', false)
            ->result();

        $product->setWas($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupCrawledImages(Product $product)
    {
        $result = $this
            ->format($product->getImages())
            ->sheer('?', false)
            ->result();

        $product->setImages($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupCrawledPortraits(Product $product)
    {
        $result = $this
            ->format($product->getPortraits())
            ->sheer('?', false)
            ->result();

        $product->setPortraits($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanupCrawledThumbnails(Product $product)
    {
        $result = $this
            ->format($product->getThumbnails())
            ->sheer('?', false)
            ->result();

        $product->setPortraits($result);
    }
}