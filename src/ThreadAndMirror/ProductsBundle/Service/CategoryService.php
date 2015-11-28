<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ThreadAndMirror\ProductsBundle\Entity\Category;
use ThreadAndMirror\ProductsBundle\Event\CategoryEvent;
use ThreadAndMirror\ProductsBundle\Repository\CategoryRepository;
use ThreadAndMirror\ProductsBundle\Service\Cache\CategoryCache;
use ThreadAndMirror\ProductsBundle\Util\StringSanitizer;

class CategoryService
{
	/** @var CategoryCache */
	protected $cache;

	/** @var CategoryRepository */
	protected $repository;

	/** @var EntityManagerInterface */
	protected $em;

	/** @var EventDispatcherInterface */
	protected $dispatcher;

	public function __construct(
		CategoryCache $cache,
		CategoryRepository $repository,
		EntityManagerInterface $em,
		EventDispatcherInterface $dispatcher
	) {
		$this->cache      = $cache;
		$this->repository = $repository;
		$this->em         = $em;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Get the ID if the category already exists
	 *
	 * @param  string        $name
	 * @param  boolean       $approximate
	 * @return string|null
	 */
	public function getExistingCategoryId($name, $approximate = false)
	{
		$slug = StringSanitizer::slugify($name);

		// Approximate match if the slug is in the list
		if ($approximate) {
			$slug = $this->approximateSlug($slug);
		}

		// Check the cache first
		$cached = $this->cache->getData($slug);

		if ($cached !== false) {
			return $cached['id'];
		}

		// Fall back the database directly
		$existing = $this->repository->findOneBy(['slug' => $slug]);

		return $existing !== null ? $existing->getId() : null;
	}

	/**
	 * Get a category for the given property and value, eg. id & 123
	 *
	 * @param  string   $field
	 * @param  mixed    $value
	 * @return Category
	 */
	public function getCategory($field, $value)
	{
		return $this->repository->findOneBy([$field => $value]);
	}

	/**
	 * Caches the category
	 *
	 * @param  Category    $category
	 */
	public function cacheCategory(Category $category)
	{
		$this->cache->setData($category->getSlug(), $category->getJson());
	}

	/**
	 * Create a new category
	 *
	 * @param Category     $category
	 */
	public function createCategory(Category $category)
	{
		$this->dispatcher->dispatch(CategoryEvent::EVENT_CREATE, new CategoryEvent($category));

		$this->em->persist($category);
		$this->em->flush();
	}

	/**
	 * Update a category
	 *
	 * @param Category     $category
	 */
	public function updateCategory(Category $category)
	{
		$this->dispatcher->dispatch(CategoryEvent::EVENT_UPDATE, new CategoryEvent($category));

		$this->em->persist($category);
		$this->em->flush();
	}

	/**
	 * Approximate the true slug using the list of acceptable deviations
	 *
	 * @param  string   $slug
	 * @return string
	 */
	public function approximateSlug($slug)
	{
		switch ($slug)
		{
			case '':
			case ' ':
			case 'new':
			case 'the-100':
				return 'uncategorised';

			case 'make-up':
				return 'cosmetics';

			case 'beachwear':
				return 'swimwear';

			case 'maternity':
				return 'maternity-wear';

			case 'shoulder':
				return 'shoulder-bags';

			case 'clutch':
				return 'clutch-bags';

			case 'dresses':
			case 'skirts':
				return 'dresses-and-skirts';

			case 'jewelry':
			case 'fine-jewelry':
				return 'jewellery';

			case 'triangle-bikini-tops':
				return 'bikini-tops';

			case 'soft-cup-bras':
				return 'bras';

			case 'coats':
			case 'jackets':
				return 'coats-and-jackets';

			case 'flat-ankle-boots':
			case 'flat-knee-boots':
			case 'high-heel-ankle-bts':
			case 'mid-heel-ankle-boots':
			case 'high-heel-knee-boots':
			case 'mid-heel-knee-boots':
			case 'over-knee-boots':
			case 'rain-boots':
			case 'wedge-boots':
				return 'boots';

			case 'day-coats':
			case 'down-coats':
			case 'evening-coats':
			case 'furs-coats':
			case 'overcoats':
			case 'padded-coats':
			case 'parkas':
			case 'peacoats':
			case 'shearling-coats':
			case 'trench-coats':
			case 'parkas':
				return 'coats';

			case 'high-heels':
			case 'mid-heels':
				return 'heels';

			case 'flat-sandals':
			case 'high-heel-sandals':
				return 'sandals';

			case 'totes':
				return 'tote-bags';

			case 'casual-shirts':
			case 'formal-shirts':
			case 'short-sleeve-shirts':
				return 'shirts';

			case 'casual-shirts':
			case 'formal-shirts':
			case 'short-sleeve-shirts':
				return 'shorts';

			case 'biker-jeans':
			case 'bootcut-jeans':
			case 'boyfriend-jeans':
			case 'cropped-jeans':
			case 'flared-jeans':
			case 'skinny-jeans':
			case 'slim-jeans':
			case 'slouch-skinny':
			case 'straight-jeans':
			case 'tapered-jeans':
				return 'jeans';

			case 'ballet-flats':
			case 'derbys':
			case 'driving-shoes':
			case 'lace-ups':
			case 'loafers':
			case 'monk-strap':
			case 'pointed-toe-flats':
			case 'slouch-skinny':
			case 'straight-jeans':
			case 'tapered-jeans':
				return 'flats';

			case 'high-top-sneakers':
			case 'low-top-sneakers':
				return 'trainers';

			case 'cardigans':
			case 'crew-necks':
			case 'heavy-cardigans':
			case 'heavy-crew-necks':
			case 'heavy-roll-necks':
			case 'light-cardigans':
			case 'light-crew-necks':
			case 'light-roll-necks':
			case 'polo-necks':
			case 'ponchos':
			case 'roll-necks':
			case 'ponchos':
			case 'shawl-necks':
			case 'v-necks':
			case 'zip-ups':
				return 'knitwear';

			case 'scarvesandties':
				return 'scarves';

			case 'eyebrow-pencils':
			case 'eyes':
				return 'eye-makeup';

			case 'face-cheek-makeup':
				return 'face-makeup';

			case 'skin-care':
				return 'skincare';


		}

		return $slug;
	}
}