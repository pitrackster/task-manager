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
        
        return $this->render('main/index.html.twig', [
            'form' => $form->createView(),
            'max' => $max,
            'active' => 'home'
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

    /**
    * @Route("/check-time", name="check_time", methods={"POST"})
    */
    public function checkTime(Request $request)
    {
        return $max;
    }
}
