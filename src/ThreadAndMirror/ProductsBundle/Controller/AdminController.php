<?php

namespace ThreadAndMirror\ProductsBundle\Controller;

// Dependencies
use Stems\CoreBundle\Controller\BaseAdminController,
	Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;

class AdminController extends BaseAdminController
{
	/**
	 * Build the sitemap entries for the bundle
	 */
	public function sitemapAction()
	{
		$em = $this->getDoctrine()->getEntityManager();

		return $this->render('ThreadAndMirrorProductsBundle:Admin:sitemap.html.twig', array());
	}

	/**
	 * Build sitemaps for the product pages
	 */
	public function productSitemapsAction(Request $request)
	{
		// find out how many products we have
		$em = $this->getDoctrine()->getEntityManager();

		$total = $em->createQuery('
			SELECT p.id
			FROM ThreadAndMirrorProductsBundle:Product p 
			WHERE p.deleted = :deleted
		')->setParameters(array(
			'deleted' 		=> false,
		))->getScalarResult();

		$sitemaps = 0;

		// create a new sitemap for each 20,000 products
		for ($i=0; $i < count($total); $i += 10000) { 
			
			// get the product names and pids
			$products = $em->createQuery('
				SELECT p.id, p.name
				FROM ThreadAndMirrorProductsBundle:Product p 
				WHERE p.deleted = :deleted
			')->setParameters(array(
				'deleted' 		=> false,
			))->setFirstResult($i)->setMaxResults(10000)->getArrayResult();

			// compile the slugs
			$slugifier = $this->get('stems.core.slugifier');
			foreach ($products as &$product) {
				$product['slug'] = $slugifier->slugify($product['name'].'-'.$product['id']);
			}

			// compile the xml
			$xml = $this->renderView('ThreadAndMirrorProductsBundle:Admin:productSitemaps.html.twig', array(
				'products'	=> $products
			));

			// save the file
			$filename = 'sitemap-products-'.$i.'to'.($i+10000).'.xml';
			$filepath = $this->get('kernel')->getRootDir().'/../web/sitemaps/'.$filename;
			$handle = fopen($filepath, 'w+');
			fwrite($handle, ''.$xml.'');
			fclose($handle);

			$sitemaps++;
		}

		$request->getSession()->getFlashBag()->set('success', $sitemaps.' product sitemaps have been updated.');
		return $this->redirect($this->generateUrl('stems_admin_core_developer_overview'));
	}

	/**
	 * @todo Build sitemaps for the designer pages
	 */
	public function designerSitemapsAction(Request $request)
	{

	}
}
