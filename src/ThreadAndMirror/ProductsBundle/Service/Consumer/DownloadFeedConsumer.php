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

class DownloadFeedConsumer implements ConsumerInterface
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
				$this->logger->info('Attempting to download '.$message['category'].' feed file for affiliate window.');
				$this->affiliateWindowService->downloadFeedFile($message['area'], $message['category']);
				break;

			case LinkshareService::KEY_NAME:
				$this->logger->info('Attempting to download merchant '.$message['merchantId'].' feed file for linkshare.');
				$this->linkshareService->downloadFeedFile($message['merchantId']);
				break;
		}
	}
}