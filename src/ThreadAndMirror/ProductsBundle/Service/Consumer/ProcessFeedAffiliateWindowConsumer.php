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

class ProcessFeedAffiliateWindowConsumer implements ConsumerInterface
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

		$this->logger->info('Attempting to process '.count($message['data']).' '.$message['category'].' feed products for affiliate window.');

		$this->affiliateWindowService->createProductsFromFeedData($message['area'], $message['category'], $message['data']);
	}
}