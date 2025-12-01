<?php

namespace App\Controller;

use App\Entity\Signature;
use App\Form\SignatureType;
use App\Repository\SignatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/signature')]
final class SignatureController extends AbstractController
{
    #[Route(name: 'app_signature_index', methods: ['GET'])]
    public function index(SignatureRepository $signatureRepository): Response
    {
        return $this->render('signature/index.html.twig', [
            'signatures' => $signatureRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_signature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $signature = new Signature($this->getUser());
        $form = $this->createForm(SignatureType::class, $signature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logoFile = $form->get('logo')->getData();

            if ($logoFile instanceof UploadedFile) {
                $filename  = \uniqid().'.'.$logoFile->guessExtension();
                $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/images';
                if (!is_dir($targetDir)) {
                    if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetDir));
                    }
                }
                $logoFile->move($targetDir, $filename);

                $imagine   = new Imagine();
                $imagePath = $targetDir.'/'.$filename;
                $image     = $imagine->open($imagePath);
                $image->resize(new Box(100, 100));

                $palette = new \Imagine\Image\Palette\RGB();
                $size    = $image->getSize();
                $mask    = $imagine->create($size, $palette->color('fff'));
                $mask->draw()
                    ->ellipse(new Point($size->getWidth() / 2, $size->getHeight() / 2), $size, $palette->color('000'), true);

                $image->applyMask($mask);
                $image->save($imagePath);

                $signature->setLogo('/uploads/images/'.$filename);
            }

            $entityManager->persist($signature);
            $entityManager->flush();

            return $this->redirectToRoute('homepage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('signature/new.html.twig', [
            'signature' => $signature,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_signature_show', methods: ['GET'])]
    public function show(Signature $signature): Response
    {
        return $this->render('signature/show.html.twig', [
            'signature' => $signature,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_signature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Signature $signature, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SignatureType::class, $signature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logoFile = $form->get('logo')->getData();

            if ($logoFile instanceof UploadedFile) {
                $filename  = \uniqid().'.'.$logoFile->guessExtension();
                $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/images';
                $logoFile->move($targetDir, $filename);

                $imagine   = new Imagine();
                $imagePath = $targetDir.'/'.$filename;
                $image     = $imagine->open($imagePath);
                $image->resize(new Box(100, 100));

                $palette = new \Imagine\Image\Palette\RGB();
                $size    = $image->getSize();
                $mask    = $imagine->create($size, $palette->color('fff'));
                $mask->draw()
                    ->ellipse(new Point($size->getWidth() / 2, $size->getHeight() / 2), $size, $palette->color('000'), true);

                $image->applyMask($mask);
                $image->save($imagePath);

                $signature->setLogo('/uploads/images/'.$filename);
            }
            $entityManager->flush();

            return $this->redirectToRoute('homepage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('signature/edit.html.twig', [
            'signature' => $signature,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_signature_delete', methods: ['POST'])]
    public function delete(Request $request, Signature $signature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$signature->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($signature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('homepage', [], Response::HTTP_SEE_OTHER);
    }
}
