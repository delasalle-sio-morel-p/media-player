<?php
/**
 * Created by PhpStorm.
 * User: lbouvet
 * Date: 19/11/2018
 * Time: 11:23
 */

namespace App\Controller;


use App\Entity\Media;
use App\Form\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Type;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function admin(EntityManagerInterface $em)
    {
        return $this->render("admin/index.html.twig", [
            'medias' => $em->getRepository(Media::class)->findBy(array('isPublished' => true),array('dateCreated' => 'DESC'))
        ]);
    }

    /**
     * @Route("/admin/media/add", name="addMedia")
     */
    public function addMedia(Request $request, EntityManagerInterface $em)
    {

        $media = new Media();
        $mediaForm = $this->createForm(MediaType::class, $media);
        $mediaForm->handleRequest($request);
        if($mediaForm->isSubmitted() && $mediaForm->isValid()){

////            $upload = $mediaForm->get('upload')->getData();
////
//            dump($upload);
            // $file stores the uploaded PDF file
            $file = $mediaForm->get('picture')->getData();
            $extension = $file->guessExtension();

            $fileName = md5(uniqid()).'.'.$extension;

            // Move the file to the directory where brochures are stored
            try {
                $file->move(
                    $this->getParameter('upload_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'brochure' property to store the PDF file name
            // instead of its contents
            $media->setPicture($fileName);
            $media->setExtension($extension);

            // ... persist the $product variable or any other work

            $media->setDateCreated(new \DateTime());
            $media->setIsPublished(true);
            $em->persist($media);
            $em->flush();
            $this->addFlash("success", "Fichier ajouté, merci!");

            return $this->redirectToRoute("admin");
        }
        return $this->render("admin/media/add.html.twig", [
            "mediaForm" => $mediaForm->createView()
        ]);
    }

    /**
     * @Route("/admin/media/edit/{id}", name="editMedia")
     */
    public function editMedia(Request $request, EntityManagerInterface $em, $id)
    {

        $repo = $this->getDoctrine()->getRepository(Media::class);

        $media = $repo->findMedia($id);
        $mediaForm = $this->createForm(MediaType::class, $media);
        $mediaForm->handleRequest($request);
        if($mediaForm->isSubmitted() && $mediaForm->isValid()){
            $media->setDateCreated(new \DateTime());
            $media->setIsPublished(true);
            $em->persist($media);
            $em->flush();
            $this->addFlash("success", "Fichier ajouté, merci!");

            return $this->redirectToRoute("admin");
        }
        return $this->render("admin/media/edit.html.twig", [
            "mediaForm" => $mediaForm->createView()
        ]);
//        return $this->render("admin/media/edit.html.twig", [
//            "media" => $media,
//            "mediaForm" => $mediaForm
//            ]);

    }

    /**
     * @Route("/admin/media/delete/{id}", name="deleteMedia")
     */
    public function delete(EntityManagerInterface $em, $id)
    {

        $media = $em->find(Media::class,$id);
        if (!$media) {
            throw $this->createNotFoundException('Aucun fichier en base a cet id');
        }
        else{
            $media->setIsPublished(false);
            $em->persist($media);
            $em->flush();
        }
        return $this->redirectToRoute("admin");
    }

}





