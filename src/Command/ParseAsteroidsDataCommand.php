<?php

namespace App\Command;

use App\Entity\Asteroid;
use Doctrine\Persistence\ManagerRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

class ParseAsteroidsDataCommand extends Command
{
    private ManagerRegistry $doctrine;

    private Client $nasaApiClient;

    private string $nasaApiKey;

    public function __construct(ManagerRegistry $doctrine, Client $client, string $nasaApiKey)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->nasaApiClient = $client;
        $this->nasaApiKey = $nasaApiKey;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:parse-asteroids')
            ->setDescription('Parse asteroids from NASA API')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startDate = (new \DateTime('-3 days'))->format('Y-m-d');
        $endDate = (new \DateTime())->format('Y-m-d');

        try {
            $request = $this->nasaApiClient->get(
                'neo/rest/v1/feed/?start_date=' . $startDate . '&end_date=' . $endDate . '&api_key=' . $this->nasaApiKey
            );

            if ($request->getStatusCode() === Response::HTTP_OK) {
                $em = $this->doctrine->getManager();
                $asteroidsData = json_decode($request->getBody()->getContents());

                if ($asteroidsData->element_count > 0) {
                    $i = 0;
                    foreach ($asteroidsData->near_earth_objects as $date => $data) {
                        foreach ($data as $asteroidData) {
                            $asteroid = (new Asteroid())
                                ->setDate(new \DateTime($date))
                                ->setName($asteroidData->name)
                                ->setReference($asteroidData->neo_reference_id)
                                ->setHazardous($asteroidData->is_potentially_hazardous_asteroid)
                                ->setSpeed(reset($asteroidData->close_approach_data)->relative_velocity->kilometers_per_hour);
                            ;

                            $em->persist($asteroid);

                            if ((++$i % 10) === 0) {
                                $em->flush();
                                $em->clear();
                                gc_collect_cycles();
                            }
                        }

                        $em->flush();
                        $em->clear();
                        gc_collect_cycles();
                    }
                }
            }

            // write about wrong status code
        } catch (GuzzleException $exception) {
            // write to logs or display on the screen
        }

        $output->writeln('Asteroids was parsed');

        return 0;
    }
}