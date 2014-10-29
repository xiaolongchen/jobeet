<?php
/**
 * Created by PhpStorm.
 * User: xiaolong.chen
 * Date: 03/10/14
 * Time: 11:38
 */

namespace Ibw\JobeetBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase{

    public function testShow()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        // get the custom parameters from app/config.yml
        $max_jobs_on_category = $kernel->getContainer()->getParameter('max_jobs_on_category');
        $max_jobs_on_homepage = $kernel->getContainer()->getParameter('max_jobs_on_homepage');

        $client = static::createClient();

        $categories = $this->em->getRepository('IbwJobeetBundle:Category')->getWithJobs();

        // categories on homepage are clickable
        foreach($categories as $category) {
            $crawler = $client->request('GET', '/en/');

            $link = $crawler->selectLink($category->getName())->link();
            $crawler = $client->click($link);

            $this->assertEquals('Ibw\JobeetBundle\Controller\CategoryController::showAction', $client->getRequest()->attributes->get('_controller'));
            $this->assertEquals($category->getSlug(), $client->getRequest()->attributes->get('slug'));

            $jobs_no = $this->em->getRepository('IbwJobeetBundle:Job')->countActiveJobs($category->getId());

            // categories with more than $max_jobs_on_homepage jobs also have a "more" link
            if($jobs_no > $max_jobs_on_homepage) {
                $crawler = $client->request('GET', '/en/');
                $link = $crawler->filter(".category_" . $category->getSlug() . " .more_jobs a")->link();
                $crawler = $client->click($link);

                $this->assertEquals('Ibw\JobeetBundle\Controller\CategoryController::showAction', $client->getRequest()->attributes->get('_controller'));
                $this->assertEquals($category->getSlug(), $client->getRequest()->attributes->get('slug'));
            }

            $pages = ceil($jobs_no/$max_jobs_on_category);

            // only $max_jobs_on_category jobs are listed
            $this->assertTrue($crawler->filter('.jobs tr')->count() <= $max_jobs_on_category);
            $this->assertRegExp("/" . $jobs_no . " jobs/", $crawler->filter('.pagination_desc')->text());

            if($pages > 1) {
                $this->assertRegExp("/page 1\/" . $pages . "/", $crawler->filter('.pagination_desc')->text());

                for ($i = 2; $i <= $pages; $i++) {
                    $link = $crawler->selectLink($i)->link();
                    $crawler = $client->click($link);

                    $this->assertEquals('Ibw\JobeetBundle\Controller\CategoryController::showAction', $client->getRequest()->attributes->get('_controller'));
                    $this->assertEquals($i, $client->getRequest()->attributes->get('page'));
                    $this->assertTrue($crawler->filter('.jobs tr')->count() <= $max_jobs_on_category);
                    if($jobs_no > 1) {
                        $this->assertRegExp("/" . $jobs_no . " jobs/", $crawler->filter('.pagination_desc')->text());
                    }
                    $this->assertRegExp("/page " . $i . "\/" . $pages . "/", $crawler->filter('.pagination_desc')->text());
                }
            }
        }
    }
} 