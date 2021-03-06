<?php

/*
 * This file is part of itk-dev/datatidy.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\DataSource;
use App\Form\Type\DataSourceType;
use App\Repository\DataSourceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/data/source")
 */
class DataSourceController extends AbstractController
{
    /**
     * @Route("/", name="data_source_index", methods={"GET"})
     */
    public function index(DataSourceRepository $dataSourceRepository): Response
    {
        return $this->render('data_source/index.html.twig', [
            'data_sources' => $dataSourceRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="data_source_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $dataSource = new DataSource();
        $form = $this->createForm(DataSourceType::class, $dataSource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($dataSource);
            $entityManager->flush();

            return $this->redirectToRoute('data_source_index');
        }

        return $this->render('data_source/new.html.twig', [
            'data_source' => $dataSource,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="data_source_show", methods={"GET"})
     */
    public function show(DataSource $dataSource): Response
    {
        return $this->render('data_source/show.html.twig', [
            'data_source' => $dataSource,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="data_source_edit", methods={"GET","POST"})
     * @IsGranted("edit", subject="dataSource")
     */
    public function edit(Request $request, DataSource $dataSource): Response
    {
        $form = $this->createForm(DataSourceType::class, $dataSource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('data_source_index');
        }

        return $this->render('data_source/edit.html.twig', [
            'data_source' => $dataSource,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="data_source_delete", methods={"DELETE"})
     */
    public function delete(Request $request, DataSource $dataSource, TranslatorInterface $translator): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete'.$dataSource->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($dataSource);
                $entityManager->flush();
            }
            $this->addFlash('success', $translator->trans('Data source %name% succesfully deleted', [
                '%name%' => $dataSource->getName(),
            ]));
        } catch (\Exception $exception) {
            $this->addFlashData(
                'danger',
                [
                    'message' => $translator->trans('Error deleting data source %name%', [
                        '%name%' => $dataSource->getName(),
                    ]),
                    'details' => $exception->getMessage(),
                ]
            );

            return $this->redirectToRoute('data_source_edit', ['id' => $dataSource->getId()]);
        }

        return $this->redirectToRoute('data_source_index');
    }
}
