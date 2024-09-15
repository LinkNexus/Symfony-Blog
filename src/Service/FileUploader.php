<?php

namespace App\Service;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class FileUploader
{

    public function __construct(private readonly SluggerInterface $slugger, private readonly ParameterBagInterface $parameterBag)
    {}

    public function moveUploadedFile(UploadedFile $file, string $entity, string $type): string|FileException
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->parameterBag->get($entity.'s_'.$type.'_directory'), $newFilename);
            return $newFilename;
        } catch (FileException $e) {
            return $e;
        }
    }
}