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

class DownloadFeedAffiliateWindowConsumer implements ConsumerInterface
{
	/** @var AffiliateWindowService */
	protected $affiliateWindowService;

	/** @var Logger */
	protected $logger;

	public function __construct(AffiliateWindowService $affiliateWindowService, Logger $logger)
	{
		$this->affiliateWindowService = $affiliateWindowService;
		$this->logger                 = $logger;
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(AMQPMessage $ampq)
	{
		$message = json_decode($ampq->body, true);

		$this->logger->info('Attempting to download '.$message['category'].' feed file for affiliate window.');

		$this->affiliateWindowService->downloadFeedFile($message['area'], $message['category']);
	}
}