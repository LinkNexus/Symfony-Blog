<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UploadAvatarFormType;
use App\Form\UploadCoverFormType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Cropperjs\Factory\CropperInterface;
use Symfony\UX\Cropperjs\Form\CropperType;

#[Route('/{slug}', name: 'app_profile_', requirements: ["slug" => "^@[a-zA-Z0-9-]+$"])]
#[IsGranted("ROLE_USER")]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Packages $assets,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        private readonly FileUploader $fileUploader,
    )
    {}

    #[Route(path: "/", name: "index")]
    public function index(
        #[MapEntity(mapping: ["slug" => "slug"])]
        ?User $user,
        Request $request,
    ): Response
    {
        if (!$user) {
            return $this->redirectToRoute("app_home");
        }

        $profilePicture = null;
        $coverPhoto = null;

        if ($user->getProfilePicture() !== null) {
            $profilePicture = $user->getProfilePicture();
        }

        if ($user->getCoverPhoto() !== null) {
            $coverPhoto = $user->getCoverPhoto();
        }

        $uploadAvatarForm = $this->createForm(UploadAvatarFormType::class);
        $uploadAvatarForm->handleRequest($request);

        $uploadCoverForm = $this->createForm(UploadCoverFormType::class);
        $uploadCoverForm->handleRequest($request);

        if (
            $uploadAvatarForm->isSubmitted() &&
            $uploadAvatarForm->isValid() &&
            $user === $this->getUser()
        ) {
            return $this->uploadImages("profile-picture", $uploadAvatarForm, $user, $profilePicture);
        } elseif (
            $uploadAvatarForm->isSubmitted() &&
            !$uploadAvatarForm->isValid()
        ) {
            $this->addFlash("danger", $uploadAvatarForm->getErrors(true));
        }

        if (
            $uploadCoverForm->isSubmitted() &&
            $uploadCoverForm->isValid() &&
            $user === $this->getUser()
        ) {
            return $this->uploadImages("cover-photo", $uploadCoverForm, $user, $coverPhoto);
        } elseif ($uploadAvatarForm->isSubmitted() && !$uploadAvatarForm->isValid()) {
            $this->addFlash("danger", $uploadAvatarForm->getErrors(true));
        }

        return $this->render('user/profile.html.twig', [
            "user" => $user,
            'uploadAvatarForm' => $uploadAvatarForm,
            "uploadCoverForm" => $uploadCoverForm,
            "cropForm" => null
        ]);
    }


    public function uploadImages(
        string $type,
        FormInterface $form,
        User $user,
        ?string $currentImage
    ): RedirectResponse
    {
        $parts = explode("-", $type);
        $uploadedImage = $form->get($parts[0] . ucfirst($parts[1]))->getData();
        $fileUploadResult = $this->fileUploader->uploadAvatarPicture($uploadedImage, $type);

        if (!($fileUploadResult instanceof Exception)) {
            if ($type === "profile-picture") {
                $user->setProfilePicture($fileUploadResult);
            } else {
                $user->setCoverPhoto($fileUploadResult);
            }

            $this->entityManager->flush();

            if ($currentImage) {
                $filesystem = new Filesystem();
                $filesystem->remove($this->projectDir . "/public/uploads/users/" . $type . "s/" . $currentImage);
            }

            return $this->redirectToRoute("app_profile_crop", [
                "image" => $fileUploadResult,
                "slug" => $this->getUser()->getSlug(),
                "type" => $type
            ]);
        }

        $this->addFlash("danger", "Oops! Something went wrong");
        return $this->redirectToRoute("app_profile_index", ["slug" => $user->getSlug()]);
    }

    #[Route("/crop/{type}/{image}", name: "crop")]
    public function crop(
        string $image,
        CropperInterface $cropper,
        Request $request,
        #[MapEntity(mapping: ["slug" => "slug"])]
        ?User $user,
        string $type = "profile-picture"
    ): Response
    {
        if (!$user) {
            return $this->redirectToRoute("app_home");
        }

        $filepath = $this->projectDir ."/public/uploads/users/". $type ."s/". $image;
        $crop = $cropper->createCrop($filepath);

        if ($type === "profile-picture") {
            $cropFormInterface = $this->createFormBuilder(["crop" => $crop])
            ->add("crop", CropperType::class, [
                "public_url" => $this->assets->getUrl("uploads/users/". $type ."s/". $image),
                'cropper_options' => [
                    'aspectRatio' => 1,
                    'preview' => '#cropper-preview',
                ],
            ]);
        } else {
            $cropFormInterface = $this->createFormBuilder(["crop" => $crop])
            ->add("crop", CropperType::class, [
                "public_url" => $this->assets->getUrl("uploads/users/". $type ."s/". $image),
                'cropper_options' => [
                    'preview' => '#cropper-preview',
                ],
            ]);
        }

        $cropForm = $cropFormInterface->getForm();
        $cropForm->handleRequest($request);

        if ($cropForm->isSubmitted()) {
            $encodedImage = $crop->getCroppedImage();
            $resource = imagecreatefromstring($encodedImage);
            imagepng($resource, $filepath);

            $this->addFlash("success", "Your profile picture has been successfully changed!");
            return $this->redirectToRoute("app_profile_index", [
                "slug" => $this->getUser()->getSlug()
            ]);
        }

        return $this->render('user/profile.html.twig', [
            "user" => $user,
            'uploadAvatarForm' => null,
            "uploadCoverForm" => null,
            "cropForm" => $cropForm
        ]);
    }

    #[Route(path: "/about", name: "about")]
    public function about(
        #[MapEntity(mapping: ["slug" => "slug"])]
        ?User $user
    ): Response
    {
        if (!$user) {
            return $this->redirectToRoute("app_home");
        }

        return $this->render('user/about.html.twig', [
            "user" => $user
        ]);
    }

}
