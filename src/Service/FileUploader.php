<?php

namespace App\Service;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class FileUploader
{

    public function __construct(private readonly SluggerInterface $slugger, private readonly ParameterBagInterface $parameterBag)
    {}

    public function fileNamer(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        return $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
    }

    public function moveUploadedFile(UploadedFile $file, string $entity, string $type): string|FileException
    {
        $newFilename = $this->fileNamer($file);

        try {
            $file->move($this->parameterBag->get($entity.'s_'.$type.'_directory'), $newFilename);
            return $newFilename;
        } catch (FileException $e) {
            return $e;
        }
    }

    public function uploadAvatarPicture(UploadedFile $file, string $type): string|FileException
    {
        $newFilename = $this->fileNamer($file);

        try {
            $path = $this->parameterBag->get("kernel.project_dir") . "/public/uploads/users/" . $type . "s/";
            $file->move($path, $newFilename);
            return $newFilename;
        } catch (FileException $e) {
            return $e;
        }
    }
}