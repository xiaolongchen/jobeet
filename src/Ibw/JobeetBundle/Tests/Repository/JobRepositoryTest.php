<?php

/**
 * @author Xiaolong CHEN <xiaolong.chen@acensi.fr>
 * @file: JobRepositoryTest
 */
namespace Ibw\JobeetBundle\Tests;

use Ibw\JobeetBundle\Entity\Job;

class JobRepositoryTest {
    public function testGetForLuceneQuery()
    {
        $em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
 
        $job = new Job();
        $job->setType('part-time');
        $job->setCompany('Sensio');
        $job->setPosition('FOO6');
        $job->setLocation('Paris');
        $job->setDescription('WebDevelopment');
        $job->setHowToApply('Send resumee');
        $job->setEmail('jobeet@example.com');
        $job->setUrl('http://sensio-labs.com');
        $job->setIsActivated(false);
 
        $em->persist($job);
        $em->flush();
 
        $jobs = $em->getRepository('IbwJobeetBundle:Job')->getForLuceneQuery('FOO6');
        $this->assertEquals(count($jobs), 0);
 
        $job = new Job();
        $job->setType('part-time');
        $job->setCompany('Sensio');
        $job->setPosition('FOO7');
        $job->setLocation('Paris');
        $job->setDescription('WebDevelopment');
        $job->setHowToApply('Send resumee');
        $job->setEmail('jobeet@example.com');
        $job->setUrl('http://sensio-labs.com');
        $job->setIsActivated(true);
 
        $em->persist($job);
        $em->flush();
 
        $jobs = $em->getRepository('IbwJobeetBundle:Job')->getForLuceneQuery('position:FOO7');
        $this->assertEquals(count($jobs), 1);
        foreach ($jobs as $job_rep) {
            $this->assertEquals($job_rep->getId(), $job->getId());
        }
 
        $em->remove($job);
        $em->flush();
 
        $jobs = $em->getRepository('IbwJobeetBundle:Job')->getForLuceneQuery('position:FOO7');
 
        $this->assertEquals(count($jobs), 0);
    }
}
