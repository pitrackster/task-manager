<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Event;
use App\Form\EventType;
use App\Service\StatsHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(EventRepository $repository, Request $request)
    {
       
        // FORM
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        $max = $this->getParameter('hours_per_day');

        if ($form->isSubmitted() && $form->isValid()) {
            $duration = $form->get('duration')->getData();
            $date = $form->get('date')->getData();

            $used = $repository->getTimeLeftForADate($date);
            $timeAvailable = $max - $used;
            // no time left for the given day
            if ($timeAvailable === 0) {
                $this->addFlash(
                    'danger',
                    'Le temps déjà enreigstré pour ce jour est déjà au maximum... vous ne pouvez pas en rajouter.'
                );
                return $this->render('main/index.html.twig', [
                    'form' => $form->createView(),
                    'max' => $max
                ]);
            }

            // duration axceed the time available for the given day
            if ($duration > $timeAvailable) {
                $this->addFlash(
                    'danger',
                    'Il ne reste que ' . $timeAvailable . ' pour la date sélectionnée'
                );
                return $this->render('main/index.html.twig', [
                    'form' => $form->createView(),
                    'max' => $max
                ]);
            }

            // the duration for the event exceed max hour per day
            if ($duration > $max) {
                $this->addFlash(
                    'danger',
                    'La durée d\'un évènement ne peut exceder ' . $max
                );
                return $this->render('main/index.html.twig', [
                    'form' => $form->createView(),
                    'max' => $max
                ]);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash(
                'info',
                'L\'évènement à bien été créé'
            );
        }

        // /FORM

        // today's data
        $today = new \DateTime();
        $todayTimeLeft = $repository->getTimeLeftForADate($today->format('Y-m-d'));
        $todayEvents = $repository->getEventsByDate($today->format('Y-m-d'));
        
        return $this->render('main/index.html.twig', [
            'form' => $form->createView(),
            'max' => $max,
            'active' => 'home',
            'todayTimeLeft' => $todayTimeLeft,
            'events' => $todayEvents
        ]);
    }

    /**
    * @Route("/stats", name="stats")
    */
    public function stats(CategoryRepository $repository, StatsHelper $statsHelper, TranslatorInterface $trans, Request $request)
    {
        $monday = strtotime("last monday");
        // si on est un lundi ajouter une semaine
        $monday = date('w', $monday) === date('w') ? $monday + 7 * 86400 : $monday;
        $friday = strtotime(date("Y-m-d", $monday)." +4 days");
 
        $start = new \DateTime(date('Y-m-d', $monday));
        $max = new \DateTime();
        $end = new \DateTime(date('Y-m-d', $friday));
 

        if ($request->query->get('start')) {
            $start = new \DateTime($request->query->get('start'));
        }

        if ($request->query->get('end')) {
            $end = new \DateTime($request->query->get('end'));
        }

        $hours_per_day = $this->getParameter('hours_per_day');
        // find categories / tasks / events in date range
        $data = $repository->findInDateRange($start, $end);
        $days = $statsHelper->getWorkingDaysForPeriod($start, $end);
        $nbHolidayDays = $statsHelper->getHolidayDaysForPeriod($start, $end);
       
       
        $periodTotalHours = $days * $hours_per_day;

        if ($request->query->get('action')) {
            $action = $request->query->get('action');
            // export to excel
            if ($action === 'export') {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                // write sheet title
                $sheet->setCellValue('A1', 'Export de l\'activité de ' . $start->format('d-m-Y') . ' à ' . $end->format('d-m-Y'));

                $line = 2;
                $datesArray = $this->getDatesFromRange($start, $end);
                foreach ($data as $cat) {
                    // write cat header
                    $sheet->setCellValue('A'.$line, $cat->getName());
                    $headerArray = $datesArray;
                    array_unshift($headerArray, $cat->getName());
                    $headerArray[] = 'Total';
                    $spreadsheet->getActiveSheet()
                        ->fromArray(
                            $headerArray,
                            null,
                            'A'.$line
                        );
                    $line++;
                    $tasks = $cat->getTasks();
                    foreach ($tasks as $task) {
                        $sheet->setCellValue('A'.$line, $task->getName());
                        // build an array of event with null values if no event for the given date
                        $events = $statsHelper->getTasksFromCatAndDates($datesArray, $task);
                        $spreadsheet->getActiveSheet()
                            ->fromArray(
                                $events,
                                null,
                                'B'.$line
                            );
                        $line++;
                    }

                    $line += 4;
                }

                $writer = new Xlsx($spreadsheet);
                // $dir = $this->getParameter('kernel.project_dir');
                $date = new \DateTime();
                $dToString = $date->format('Ymd_His');
                $filename = $dToString . '_export.xlsx';
                // download file
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header("Content-Disposition: attachment; filename=\"{$filename}\"");
                $writer->save('php://output');
                exit();
                // $writer->save($dir . '/var/' .  $filename);
            }
        }

        return $this->render('main/stats.html.twig', [
            'data' =>   $data,
            'start' => $start,
            'end' => $end,
            'max' => $max,
            'workingDays' => $days,
            'holidayDays' => $nbHolidayDays,
            'hours' => $periodTotalHours,
            'hoursPerDay' => $hours_per_day,
            'active' => 'stats'
        ]);
    }

    public function getDatesFromRange($start, $end, $format = 'd-m-Y')
    {
      
        // Declare an empty array
        $array = array();
          
        // Variable that store the date interval
        // of period 1 day
        $interval = new \DateInterval('P1D');
      
        $realEnd = $end;
        $realEnd->add($interval);
      
        $period = new \DatePeriod($start, $interval, $realEnd);
      
        // Use loop to store date into array
        foreach ($period as $date) {
            $array[] = $date->format($format);
        }
      
        // Return the array elements
        return $array;
    }

   

    /**
    * @Route("/check-time", name="check_time", methods={"POST"})
    */
    public function checkTime(Request $request)
    {
        return $max;
    }
}
