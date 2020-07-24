<?php

namespace App\Controller;

use App\Entity\Holiday;
use App\Form\HolidayType;
use App\Repository\HolidayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/holiday")
 */
class HolidayController extends AbstractController
{
    /**
     * @Route("/", name="holiday_index", methods={"GET"})
     */
    public function index(HolidayRepository $holidayRepository): Response
    {
        return $this->render('holiday/index.html.twig', [
            'holidays' => $holidayRepository->findAll(),
            'active' => 'holidays'
        ]);
    }

    /**
     * @Route("/new", name="holiday_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $holiday = new Holiday();
        $form = $this->createForm(HolidayType::class, $holiday);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($holiday);
            $entityManager->flush();

            return $this->redirectToRoute('holiday_index');
        }

        return $this->render('holiday/new.html.twig', [
            'holiday' => $holiday,
            'form' => $form->createView(),
            'active' => 'holidays'
        ]);
    }

    /**
     * @Route("/{id}", name="holiday_show", methods={"GET"})
     */
    public function show(Holiday $holiday): Response
    {
        return $this->render('holiday/show.html.twig', [
            'holiday' => $holiday,
            'active' => 'holidays'
        ]);
    }

    /**
     * @Route("/{id}/edit", name="holiday_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Holiday $holiday): Response
    {
        $form = $this->createForm(HolidayType::class, $holiday);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('holiday_index');
        }

        return $this->render('holiday/edit.html.twig', [
            'holiday' => $holiday,
            'form' => $form->createView(),
            'active' => 'holidays'
        ]);
    }

    /**
     * @Route("/{id}", name="holiday_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Holiday $holiday): Response
    {
        if ($this->isCsrfTokenValid('delete'.$holiday->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($holiday);
            $entityManager->flush();
        }

        return $this->redirectToRoute('holiday_index');
    }
}
