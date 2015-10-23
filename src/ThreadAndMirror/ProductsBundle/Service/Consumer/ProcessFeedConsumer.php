<?php
/**
 * Created by PhpStorm.
 * User: Ste
 * Date: 20/09/2015
 * Time: 19:57
 */

namespace ThreadAndMirror\ProductsBundle\Service\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bridge\Monolog\Logger;
use ThreadAndMirror\ProductsBundle\Service\Affiliate\AffiliateWindowService;
use ThreadAndMirror\ProductsBundle\Service\Affiliate\LinkshareService;

class ProcessFeedConsumer implements ConsumerInterface
{
	/** @var AffiliateWindowService */
	protected $affiliateWindowService;

	/** @var LinkshareService */
	protected $linkshareService;

	/** @var Logger */
	protected $logger;

	public function __construct(AffiliateWindowService $affiliateWindowService, LinkshareService $linkshareService, Logger $logger)
	{
		$this->affiliateWindowService = $affiliateWindowService;
		$this->linkshareService       = $linkshareService;
		$this->logger                 = $logger;
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(AMQPMessage $ampq)
	{
		$message   = json_decode($ampq->body, true);
		$affiliate = $message['affiliate'];

		switch ($affiliate)
		{
			case AffiliateWindowService::KEY_NAME:
				$this->logger->info('Attempting to process '.$message['category'].' feed data for affiliate window.');
				$this->affiliateWindowService->createProductsFromFeedData($message['data'], $message['area'], $message['category']);
				break;

			case LinkshareService::KEY_NAME:
				$this->logger->info('Attempting to process merchant '.$message['merchantId'].' feed data for linkshare.');
				$this->linkshareService->createProductsFromFeedData($message['data'], $message['merchantId']);
				break;
		}
	}
}