<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class TopshopCrawler extends AbstractCrawler
{

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
        return 'Topshop';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCategoryName(DomCrawler $crawler)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPid(DomCrawler $crawler)
    {
        return $this->getTextFromElement($crawler, '#productInfo li.product_code span');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDescription(DomCrawler $crawler)
    {
        return $this->getTextFromElement($crawler, '#productInfo p');
    }

    /**
     * {@inheritdoc}
     */
    protected function getNow(DomCrawler $crawler)
    {
        return $this->getTextFromAlternatingElements($crawler, '#product-detail .now_price', '#product-detail .product_price');
    }

    /**
     * {@inheritdoc}
     */
    protected function getWas(DomCrawler $crawler)
    {
        return $this->getTextFromAlternatingElements($crawler, '#product-detail .was_price', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function getImages(DomCrawler $crawler)
    {
        return $this->getSrcFromList($crawler, '#product-detail .hero_image_link img');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableSizes(DomCrawler $crawler)
    {
        return $this->getTextFromList($crawler, '.wrapper_product_size_grid .product_size_buttons label.btn');
    }

    /**
     * {@inheritdoc}
     */
    protected function getStockedSizes(DomCrawler $crawler)
    {
        return $this->getTextFromList($crawler, '.wrapper_product_size_grid .product_size_buttons label.btn:not(.disabled)');
    }

    /**
     * {@inheritdoc}
     */
    protected function getStyleWith(DomCrawler $crawler)
    {
        return null;
    }
}