<?php
/**
 * Created by PhpStorm.
 * User: lbouvet
 * Date: 19/11/2018
 * Time: 11:23
 */

namespace App\Controller;


use App\Entity\Genre;
use App\Entity\Media;
use App\Entity\TypeMedia;
use App\Entity\Utilisateur;
use App\Form\GenreType;
use App\Form\MediaType;
use App\Form\TypeMediaType;
use App\Form\UtilisateurType;
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
    //--------------------------------------------------------------------------------------------------
    //------------------------------------- Management Media -------------------------------------------
    //--------------------------------------------------------------------------------------------------
    /**
     * @Route("/admin", name="admin")
     */
    public function admin(EntityManagerInterface $em)
    {
        return $this->render("admin/index.html.twig", [
            'medias' => $em->getRepository(Media::class)->findBy(array('isPublished' => true), array('dateCreated' => 'DESC'))
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
        if ($mediaForm->isSubmitted() && $mediaForm->isValid()) {

            // $file stores the uploaded PDF file
            $file = $mediaForm->get('picture')->getData();
            $contenu = $mediaForm->get('media')->getData();

            $extension = $contenu->guessExtension();

            $uniqName = md5(uniqid());
            $fileName = $uniqName . '_picture' . '.' . $file->guessExtension();
            $contenuName = $uniqName . '_media' . '.' . $extension;

            // Move the file to the directory where brochures are stored
            try {
                $file->move(
                    $this->getParameter('upload_directory'),
                    $fileName
                );
                $contenu->move(
                    $this->getParameter('upload_directory'),
                    $contenuName
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
        $media = $em->find(Media::class, $id);

        $mediaForm = $this->createForm(MediaType::class, $media);
        $mediaForm->handleRequest($request);
        if ($mediaForm->isSubmitted() && $mediaForm->isValid()) {
            $file = $mediaForm->get('picture')->getData();
            $contenu = $mediaForm->get('media')->getData();
            if ($file != null) {
                dump($media);
                $temp = explode("_", $media->getPicture());
                $uniqName = $temp[0];
                $fileName = $uniqName . '_picture' . '.' . $file->guessExtension();
                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('upload_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }
            if ($contenu !== null) {
                $temp = explode("_", $media->getPicture());
                $uniqName = $temp[0];
                $extension = $contenu->guessExtension();
                $contenuName = $uniqName . '_media' . '.' . $extension;
                try {
                    $contenu->move(
                        $this->getParameter('upload_directory'),
                        $contenuName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }
            $media->setPicture($fileName);
            $media->setExtension($extension);

            $media->setDateCreated(new \DateTime());
            $media->setIsPublished(true);
            $em->persist($media);
            $em->flush();
            $this->addFlash("success", "Fichier modifié, merci!");

            return $this->redirectToRoute("admin");
        }
        return $this->render("admin/media/edit.html.twig", [
            "media" => $media,
            "mediaForm" => $mediaForm->createView()
        ]);
    }

    /**
     * @Route("/admin/media/delete/{id}", name="deleteMedia")
     */
    public function deleteMedia(EntityManagerInterface $em, $id)
    {

        $media = $em->find(Media::class, $id);
        if (!$media) {
            throw $this->createNotFoundException('Aucun fichier en base a cet id');
        } else {
            $media->setIsPublished(false);
            $em->persist($media);
            $em->flush();
        }
        return $this->redirectToRoute("admin");
    }


    //--------------------------------------------------------------------------------------------------
    //------------------------------------- Management Utilisateur -------------------------------------
    //--------------------------------------------------------------------------------------------------
    /**
     * @Route("/admin/user/list", name="listUsers")
     */
    public function listUsers(EntityManagerInterface $em)
    {
        return $this->render("admin/utilisateur/list.html.twig", [
            'users' => $em->getRepository(Utilisateur::class)->findAll()
        ]);
    }

    /**
     * @Route("/admin/user/add", name="addUser")
     */
    public function addUser(Request $request, EntityManagerInterface $em)
    {

        $user = new Utilisateur();
        $userForm = $this->createForm(UtilisateurType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            return $this->redirectToRoute("listUsers");
        }
        return $this->render("admin/utilisateur/add.html.twig", [
            "userForm" => $userForm->createView()
        ]);
    }

    /**
     * @Route("/admin/user/edit/{id}", name="editUser")
     */
    public function editUser(Request $request, EntityManagerInterface $em, $id)
    {
        $user = $em->find(Utilisateur::class, $id);

        $userForm = $this->createForm(UtilisateurType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            return $this->redirectToRoute("listUsers");
        }
        return $this->render("admin/utilisateur/edit.html.twig", [
            "user" => $user,
            "userForm" => $userForm->createView()
        ]);
    }

    /**
     * @Route("/admin/user/delete/{id}", name="deleteUser")
     */
    public function deleteUser(EntityManagerInterface $em, $id)
    {

        $media = $em->find(Utilisateur::class, $id);
        if (!$media) {
            throw $this->createNotFoundException('Aucun utilisateur en base avec cet id');
        } else {

        }
        return $this->redirectToRoute("listUsers");
    }


    //--------------------------------------------------------------------------------------------------
    //------------------------------------- Management Genre -------------------------------------------
    //--------------------------------------------------------------------------------------------------
    /**
     * @Route("/admin/genre/list", name="listGenres")
     */
    public function listGenres(EntityManagerInterface $em)
    {
        return $this->render("admin/genre/list.html.twig", [
            'genres' => $em->getRepository(Genre::class)->findAll()
        ]);
    }

    /**
     * @Route("/admin/genre/add", name="addGenre")
     */
    public function addGenre(Request $request, EntityManagerInterface $em)
    {

        $genre = new Genre();
        $genreForm = $this->createForm(GenreType::class, $genre);
        $genreForm->handleRequest($request);
        if ($genreForm->isSubmitted() && $genreForm->isValid()) {
            return $this->redirectToRoute("listGenres");
        }
        return $this->render("admin/utilisateur/add.html.twig", [
            "genreForm" => $genreForm->createView()
        ]);
    }

    /**
     * @Route("/admin/genre/edit/{id}", name="editGenre")
     */
    public function editGenre(Request $request, EntityManagerInterface $em, $id)
    {
        $genre = $em->find(Genre::class, $id);

        $genreForm = $this->createForm(GenreType::class, $genre);
        $genreForm->handleRequest($request);
        if ($genreForm->isSubmitted() && $genreForm->isValid()) {
            return $this->redirectToRoute("listGenres");
        }
        return $this->render("admin/genre/edit.html.twig", [
            "genre" => $genre,
            "genreForm" => $genreForm->createView()
        ]);
    }

    /**
     * @Route("/admin/genre/delete/{id}", name="deleteGenre")
     */
    public function deleteGenre(EntityManagerInterface $em, $id)
    {

        $genre = $em->find(Genre::class, $id);
        if (!$genre) {
            throw $this->createNotFoundException('Aucun genre en base avec cet id');
        } else {

        }
        return $this->redirectToRoute("listGenres");
    }



    //--------------------------------------------------------------------------------------------------
    //------------------------------------- Management TypeMedia -------------------------------------------
    //--------------------------------------------------------------------------------------------------
    /**
     * @Route("/admin/typeMedia/list", name="listTypeMedia")
     */
    public function listTypeMedia(EntityManagerInterface $em)
    {
        return $this->render("admin/typeMedia/list.html.twig", [
            'typeMedia' => $em->getRepository(TypeMedia::class)->findAll()
        ]);
    }

    /**
     * @Route("/admin/typeMedia/add", name="addTypeMedia")
     */
    public function addTypeMedia(Request $request, EntityManagerInterface $em)
    {
        $typeMedia = new TypeMedia();
        $typeMediaForm = $this->createForm(TypeMediaType::class, $typeMedia);
        $typeMediaForm->handleRequest($request);
        if ($typeMediaForm->isSubmitted() && $typeMediaForm->isValid()) {
            return $this->redirectToRoute("listTypeMedia");
        }
        return $this->render("admin/typeMedia/add.html.twig", [
            "typeMediaForm" => $typeMediaForm->createView()
        ]);
    }

    /**
     * @Route("/admin/typeMedia/edit/{id}", name="editTypeMedia")
     */
    public function editTypeMedia(Request $request, EntityManagerInterface $em, $id)
    {
        $typeMedia = $em->find(TypeMedia::class, $id);

        $typeMediaForm = $this->createForm(TypeMediaType::class, $typeMedia);
        $typeMediaForm->handleRequest($request);
        if ($typeMediaForm->isSubmitted() && $typeMediaForm->isValid()) {
            return $this->redirectToRoute("listTypeMedia");
        }
        return $this->render("admin/typeMedia/edit.html.twig", [
            "typeMedia" => $typeMedia,
            "typeMediaForm" => $typeMediaForm->createView()
        ]);
    }

    /**
     * @Route("/admin/typeMedia/delete/{id}", name="deleteTypeMedia")
     */
    public function deleteTypeMedia(EntityManagerInterface $em, $id)
    {

        $typeMedia = $em->find(TypeMedia::class, $id);
        if (!$typeMedia) {
            throw $this->createNotFoundException('Aucun typeMedia en base avec cet id');
        } else {

        }
        return $this->redirectToRoute("listTypeMedia");
    }
}





