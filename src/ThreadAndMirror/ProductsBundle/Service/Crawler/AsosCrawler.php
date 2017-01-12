<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class AsosCrawler extends AbstractCrawler
{
    /**
     * {@inheritdoc}
     */
    protected function getPid(DomCrawler $crawler)
    {
        return $this->getTextFromElement($crawler, '#product-details .product-code span');
    }

    /**
     * {@inheritdoc}
     */
    protected function getName(DomCrawler $crawler)
    {
        return $this->getTextFromElement($crawler, 'h1');
    }

    /**
     * {@inheritdoc}
     */
    protected function getBrandName(DomCrawler $crawler)
    {
        return $this->getTextFromElement($crawler, '#product-details .product-description span a strong', 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCategory(DomCrawler $crawler)
    {
        return $this->getTextFromElement($crawler, '#product-details .product-description span a strong', 1);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDescription(DomCrawler $crawler)
    {
        return $this->getTextFromList($crawler, '#product-details .product-description ul li');
    }

    /**
     * {@inheritdoc}
     */
    protected function getNow(DomCrawler $crawler)
    {
        return $this->getTextFromElement($crawler, 'section script', 3);
    }

    /**
     * {@inheritdoc}
     */
    protected function getWas(DomCrawler $crawler)
    {
        return $this->getTextFromElement($crawler, 'section script', 3);
    }

    /**
     * {@inheritdoc}
     */
    protected function getImages(DomCrawler $crawler)
    {
        return $this->getSrcFromList($crawler, '#gallery-content .thumbnails li img');
    }

    /**
     * {@inheritdoc}
     */
    protected function getPortraits(DomCrawler $crawler)
    {
        return $this->getSrcFromList($crawler, '#gallery-content .thumbnails li img');
    }

    /**
     * {@inheritdoc}
     */
    protected function getThumbnails(DomCrawler $crawler)
    {
        return $this->getSrcFromList($crawler, '#gallery-content .thumbnails li img');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableSizes(DomCrawler $crawler)
    {
        return $this->getTextFromList($crawler, '#core-product .colour-size-select option');
    }

    /**
     * {@inheritdoc}
     */
    protected function getStockedSizes(DomCrawler $crawler)
    {
        return $this->getTextFromList($crawler, '#core-product .colour-size-select option:not([disabled])');
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomHeaders()
    {
        return ['Cookie' => 'asos=currencyid=1'];
    }
}