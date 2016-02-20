<?php
/**
 * Created by PhpStorm.
 * User: eXPert
 * Date: 16.02.2016
 * Time: 23:39
 */

namespace CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use CatalogBundle\Entity as Entity;

class CatalogUpdateCommand extends ContainerAwareCommand
{
    /**
     * @var String
     */
    private $API_2GIS_KEY;

    /**
     * @var SymfonyStyle
     */
    private $io;

    const maxFirmsCount = 100000;

    protected function configure()
    {
        $this
            ->setName('catalog:update')
            ->setDescription('Update Catalog DB')
            ->addArgument(
                'source',
                InputArgument::OPTIONAL,
                'Which source? [random] - for random set of data, [2gis] - get data from 2GIS API',
                '2gis'
            )
            ->addArgument(
                'city',
                InputArgument::OPTIONAL,
                'Which city to parse?',
                'Новосибирск'
            )
            ->addArgument(
                'key',
                InputArgument::OPTIONAL,
                '2GIS API KEY'
            )
            ->addOption(
                'clear',
                null,
                InputOption::VALUE_NONE,
                'Clear DB before updating'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Updating Catalog DB');

        if ($input->getOption('clear')) {
            $this->io->section('Clear DB');
            $this->io->text('Starting commands...');

            $this->getApplication()->find('doctrine:database:drop')->run(new ArrayInput(['command' => 'doctrine:database:drop', '--force' => true]), $output);
            $this->getApplication()->find('doctrine:database:create')->run(new ArrayInput(['command' => 'doctrine:database:create']), $output);
            $this->getApplication()->find('doctrine:schema:update')->run(new ArrayInput(['command' => 'doctrine:schema:update', '--force' => true]), $output);

            $this->io->text('Clear done!');
        }

        $this->io->section('Updating DB');

        $firmRepository = $this->getContainer()->get('doctrine')->getRepository('CatalogBundle:Firm');

        if ($firmRepository->countAll() >= self::maxFirmsCount) {
            $this->io->note('You already have ' . self::maxFirmsCount . ' firms!');
            exit();
        }

        $source = $input->getArgument('source');

        if ($source == '2gis') {
            $this->API_2GIS_KEY = $input->getArgument('key') ? $input->getArgument('key') : $this->io->askHidden('Please enter 2GIS API KEY');

            $city = $input->getArgument('city') ? $input->getArgument('city') : $this->io->ask('Please enter city to parse');

            $this->io->text("Getting info from 2gis...");

            $this->process2GISLocations([$city]);
        } else if ($source == 'random') {
            $this->io->text("Start updating DB by random data...");

            $this->io->note('Method not implemented! Breaking updating process..');

            exit();
        } else {
            throw new InvalidArgumentException('Bad value for [source] argument!');
        }

        $this->io->newLine();
        $this->io->success('Catalog was updated!');
    }

    protected function process2GISLocations($locations)
    {
        $rubricRepository = $this->getContainer()->get('doctrine')->getRepository('CatalogBundle:Rubric');

        foreach ($locations as $location) {
            $this->io->writeLn('Location: ' . $location);

            $queryParams = [
                'where' => $location,
                'show_children' => 1,
                'key' => $this->API_2GIS_KEY,
                'version' => '1.3',
                'lang' => 'ru',
                'output' => 'json'
            ];

            if ($response = file_get_contents('http://catalog.api.2gis.ru/rubricator?' . http_build_query($queryParams))) {
                if (is_object($response = json_decode($response))) {
                    if ($response->response_code == 200) {
                        if ($response->total) {
                            foreach ($response->result as $result) {
                                $rubric = new Entity\Rubric();

                                $rubric->setName($result->name);

                                $rubric = $rubricRepository->insertUnique($rubric);

                                if (isset($result->children) && $result->children) {
                                    foreach ($result->children as $child) {
                                        $childRubric = new Entity\Rubric();

                                        $childRubric
                                            ->setName($child->name)
                                            ->setParent($rubric);

                                        $rubricRepository->insertUnique($childRubric);

                                        $this->process2GISRubric($childRubric, $location);
                                    }
                                } else {
                                    $this->process2GISRubric($rubric, $location);
                                }
                            }
                        }
                    } else {
                        throw new RuntimeException($response->error_message, $response->response_code);
                    }
                }
            }
        }
    }

    /**
     * @param Entity\Rubric $rubric
     * @param String $where
     */
    protected function process2GISRubric(Entity\Rubric $rubric, $where)
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $em = $doctrine->getEntityManager();

        $buildingRepository = $doctrine->getRepository('CatalogBundle:Building');
        $firmRepository = $doctrine->getRepository('CatalogBundle:Firm');
        $rubricRepository = $doctrine->getRepository('CatalogBundle:Rubric');
        $phoneRepository = $doctrine->getRepository('CatalogBundle:Phone');

        $page = 1;
        $pageSize = 50;

        $this->io->writeln('Rubric: ' . $rubric->getName());

        do {
            $queryParams = [
                'what' => $rubric->getName(),
                'where' => $where,
                'page' => $page,
                'pagesize' => $pageSize,
                //'limit' => 2000,
                'sort' => 'relevance',
                'key' => $this->API_2GIS_KEY,
                'version' => '1.3',
                'lang' => 'ru',
                'output' => 'json'
            ];

            $queryDateStart = new \DateTime();

            $query = 'http://catalog.api.2gis.ru/searchinrubric?' . http_build_query($queryParams);

            //$this->io->writeln("Query: " . urldecode($query));

            if ($response = file_get_contents($query)) {
                if (is_object($response = json_decode($response))) {
                    if ($response->response_code == 200) {
                        if ($response->total) {
                            $total = $response->total;

                            $pages = ceil($total / $pageSize);

                            $this->io->writeln($page . ' / ' . $pages . ' - ' . (($page - 1) * $pageSize + count($response->result)) . ' / ' . $total . ' Server response time, s: ' . ($queryDateStart->diff(new \DateTime(), true)->s));

                            if ($page < $pages) {
                                $page++;
                            } else {
                                $page = 0;
                            }

                            foreach ($response->result as $result) {
                                if (!isset($result->address) || !isset($result->lat)) {
                                    continue;
                                }

                                $building = new Entity\Building();

                                $building
                                    ->setAddress($result->address)
                                    ->setCity($result->city_name)
                                    ->setLat($result->lat)
                                    ->setLng($result->lon);

                                $building = $buildingRepository->insertUnique($building);

                                if (!$firmRepository->findOneBy(['name' => $result->name])) {
                                    $firm = new Entity\Firm();

                                    $firm
                                        ->setName($result->name)
                                        ->setBuilding($building);

                                    foreach ($result->rubrics as $rubricName) {
                                        $rubric = new Entity\Rubric();

                                        $rubric->setName($rubricName);

                                        $rubric = $rubricRepository->insertUnique($rubric);

                                        $firm->addRubric($rubric);
                                    }

                                    $em->persist($firm);

                                    $em->flush();

                                    /******************************************************************
                                     *                        Get phones
                                     ******************************************************************/

                                    $firmInfoQueryParams = [
                                        'id' => $result->id,
                                        'key' => $this->API_2GIS_KEY,
                                        'version' => '1.3',
                                        'lang' => 'ru',
                                        'output' => 'json'
                                    ];

                                    $firmInfoQuery = 'http://catalog.api.2gis.ru/profile?' . http_build_query($firmInfoQueryParams);

                                    if ($response = file_get_contents($firmInfoQuery)) {
                                        if (is_object($response = json_decode($response))) {
                                            if ($response->response_code == 200) {
                                                foreach ($response->contacts as $contactGroup) {
                                                    foreach ($contactGroup->contacts as $contact) {
                                                        if ($contact->type == 'phone') {
                                                            $phone = new Entity\Phone();

                                                            $phone
                                                                ->setNumber($contact->value)
                                                                ->setFirm($firm);

                                                            $phoneRepository->insertUnique($phone);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    /*********************************************************************/

                                    if ($firmRepository->countAll() == self::maxFirmsCount) {
                                        $this->io->success('Successfully get ' . self::maxFirmsCount . ' firms!');
                                        exit();
                                    }
                                }
                            }
                        }
                    } elseif ($response->response_code == 400 && $response->error_code == 'incorrectPage') {
                        $this->io->writeln('Incorrect Page! Break...');
                        break;
                    } else {
                        throw new RuntimeException($response->error_message, $response->response_code);
                    }
                }
            }

            $this->io->writeln('Search on page time, s: ' . ($queryDateStart->diff(new \DateTime(), true)->s));

            gc_collect_cycles();
            //sleep(0.1);
        } while ($page != 0);
    }
}