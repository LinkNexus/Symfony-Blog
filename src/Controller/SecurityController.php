<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use MobileDetectBundle\DeviceDetector\MobileDetectorInterface;
use MobileDetectBundle\MobileDetectBundle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;

class SecurityController extends AbstractController
{
    public function __construct(private readonly Security $security)
    {}

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/login/check', name: 'app_login_check')]
    public function check(Request $request): Response
    {
        // get the login link query parameters
        $expires = $request->query->get('expires');
        $username = $request->query->get('user');
        $hash = $request->query->get('hash');

        if (!$expires || !$username || !$hash) {
            return $this->redirectToRoute('app_login');
        }

        // and render a template with the button
        return $this->render('security/process_login_link.html.twig', [
            'expires' => $expires,
            'user' => $username,
            'hash' => $hash
        ]);
    }

    #[Route('/login/request', name: 'app_request_login_link')]
    public function requestLoginLink(
        NotifierInterface $notifier,
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $this->security->getUser();

        if ($user instanceof User && $user->isVerified()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            // load the user in some way (e.g., using the form input)
            $email = $request->getPayload()->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user instanceof User) {
                $user->setLastLinkRequestedAt(new \DateTimeImmutable());
                $entityManager->flush();

                //create a login link for $user, this returns an instance of
                // LoginLinkDetails
                $loginLinkDetails = $loginLinkHandler->createLoginLink($user);
                $loginLink = $loginLinkDetails->getUrl();

                // create a notification based on the login link details
                $notification = new LoginLinkNotification(
                    $loginLinkDetails,
                    'Welcome to Nexus'
                );

                // create a recipient for this user
                $recipient = new Recipient($user->getEmail());

                // send the notification to the user
                $notifier->send($notification, $recipient);
            }

            $this->addFlash('success', 'A Mail containing the instructions for your Login has been successfully sent to the provided Email Address if an existing Account was linked to it');
            return $this->redirectToRoute('app_request_login_link');
        }

        return $this->render('security/request_login_link.html.twig');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/categories', name: 'app_cat')]
    public function categories(EntityManagerInterface $entityManager) {
        $categories = [
            // Existing 50 categories
            ['name' => 'AI & Machine Learning', 'slug' => 'ai-machine-learning'],
            ['name' => 'Blockchain & Cryptocurrency', 'slug' => 'blockchain-cryptocurrency'],
            ['name' => 'Software Development', 'slug' => 'software-development'],
            ['name' => 'Programming Languages', 'slug' => 'programming-languages'],
            ['name' => 'Mobile App Development', 'slug' => 'mobile-app-development'],
            ['name' => 'Cybersecurity & Privacy', 'slug' => 'cybersecurity-privacy'],
            ['name' => 'Cloud Computing', 'slug' => 'cloud-computing'],
            ['name' => 'Augmented Reality (AR) & Virtual Reality (VR)', 'slug' => 'augmented-reality-virtual-reality'],
            ['name' => 'Quantum Computing', 'slug' => 'quantum-computing'],
            ['name' => 'Big Data & Analytics', 'slug' => 'big-data-analytics'],
            ['name' => 'Tech Startups & Innovation', 'slug' => 'tech-startups-innovation'],
            ['name' => 'Internet of Things (IoT)', 'slug' => 'internet-of-things'],
            ['name' => 'Web Development', 'slug' => 'web-development'],
            ['name' => 'Tech Industry News', 'slug' => 'tech-industry-news'],
            ['name' => 'Data Science & Artificial Intelligence', 'slug' => 'data-science-artificial-intelligence'],
            ['name' => 'Product Reviews', 'slug' => 'product-reviews'],
            ['name' => 'Gadgets & Hardware', 'slug' => 'gadgets-hardware'],
            ['name' => 'Open Source Projects', 'slug' => 'open-source-projects'],
            ['name' => '5G & Networking Technologies', 'slug' => '5g-networking-technologies'],
            ['name' => 'Automation & Robotics', 'slug' => 'automation-robotics'],
            ['name' => 'Future Tech & Predictions', 'slug' => 'future-tech-predictions'],
            ['name' => 'Tech Tutorials & Guides', 'slug' => 'tech-tutorials-guides'],
            ['name' => 'User Experience (UX) & User Interface (UI) Design', 'slug' => 'user-experience-ui-design'],
            ['name' => 'Wearables & Smart Devices', 'slug' => 'wearables-smart-devices'],
            ['name' => 'Green Tech & Sustainable Technology', 'slug' => 'green-tech-sustainable-technology'],
            ['name' => 'Tech for Social Good', 'slug' => 'tech-for-social-good'],
            ['name' => 'Digital Transformation', 'slug' => 'digital-transformation'],
            ['name' => 'Startups & Venture Capital', 'slug' => 'startups-venture-capital'],
            ['name' => 'Productivity Tools & Apps', 'slug' => 'productivity-tools-apps'],
            ['name' => 'Tech Events & Conferences', 'slug' => 'tech-events-conferences'],
            ['name' => 'Ethics in Technology', 'slug' => 'ethics-in-technology'],
            ['name' => 'Tech Career Advice & Resources', 'slug' => 'tech-career-advice-resources'],
            ['name' => 'Tech Investment & Finance', 'slug' => 'tech-investment-finance'],
            ['name' => 'E-commerce & Digital Payment Solutions', 'slug' => 'ecommerce-digital-payment-solutions'],
            ['name' => 'Gaming & Game Development', 'slug' => 'gaming-game-development'],
            ['name' => 'Tech Laws & Regulations', 'slug' => 'tech-laws-regulations'],
            ['name' => 'Cloud Storage & Data Management', 'slug' => 'cloud-storage-data-management'],
            ['name' => 'DevOps & Continuous Integration', 'slug' => 'devops-continuous-integration'],
            ['name' => 'Tech Myths Debunked', 'slug' => 'tech-myths-debunked'],
            ['name' => 'Smart Homes & Home Automation', 'slug' => 'smart-homes-home-automation'],
            ['name' => 'Digital Art & Creativity Tools', 'slug' => 'digital-art-creativity-tools'],
            ['name' => 'Augmented Analytics', 'slug' => 'augmented-analytics'],
            ['name' => 'Streaming & Entertainment Technology', 'slug' => 'streaming-entertainment-technology'],
            ['name' => 'Tech Trends & Innovations', 'slug' => 'tech-trends-innovations'],
            ['name' => 'IT & Infrastructure', 'slug' => 'it-infrastructure'],
            ['name' => 'Hackathons & Coding Competitions', 'slug' => 'hackathons-coding-competitions'],
            ['name' => 'Tech for Education', 'slug' => 'tech-for-education'],
            ['name' => 'Online Security Tips', 'slug' => 'online-security-tips'],
            ['name' => 'Tech History & Evolution', 'slug' => 'tech-history-evolution'],
            ['name' => 'Virtual Collaboration Tools', 'slug' => 'virtual-collaboration-tools'],

            // 25 New Categories
            ['name' => 'Edge Computing', 'slug' => 'edge-computing'],
            ['name' => 'Self-Driving Cars', 'slug' => 'self-driving-cars'],
            ['name' => 'Drone Technology', 'slug' => 'drone-technology'],
            ['name' => 'Digital Twins', 'slug' => 'digital-twins'],
            ['name' => 'Smart Cities', 'slug' => 'smart-cities'],
            ['name' => 'Biotechnology & Tech', 'slug' => 'biotechnology-tech'],
            ['name' => 'Voice Assistants & Natural Language Processing (NLP)', 'slug' => 'voice-assistants-nlp'],
            ['name' => 'Digital Health & Wearable Tech', 'slug' => 'digital-health-wearable-tech'],
            ['name' => 'Neural Interfaces & Brain-Computer Interaction', 'slug' => 'neural-interfaces-brain-computer-interaction'],
            ['name' => 'Mixed Reality (MR)', 'slug' => 'mixed-reality'],
            ['name' => '3D Printing & Additive Manufacturing', 'slug' => '3d-printing-additive-manufacturing'],
            ['name' => 'Computer Vision', 'slug' => 'computer-vision'],
            ['name' => 'Tech for Climate Change', 'slug' => 'tech-for-climate-change'],
            ['name' => 'Low-Code & No-Code Platforms', 'slug' => 'low-code-no-code-platforms'],
            ['name' => 'AI-Powered Automation', 'slug' => 'ai-powered-automation'],
            ['name' => 'Tech in Space Exploration', 'slug' => 'tech-in-space-exploration'],
            ['name' => 'Cryptography & Digital Signatures', 'slug' => 'cryptography-digital-signatures'],
            ['name' => 'OpenAI & GPT Technology', 'slug' => 'openai-gpt-technology'],
            ['name' => 'Metaverse & Virtual Worlds', 'slug' => 'metaverse-virtual-worlds'],
            ['name' => 'Digital Identity & Decentralized Identity', 'slug' => 'digital-identity-decentralized-identity'],
            ['name' => 'Hyperautomation', 'slug' => 'hyperautomation'],
            ['name' => 'NFTs & Digital Ownership', 'slug' => 'nfts-digital-ownership'],
            ['name' => 'Robotic Process Automation (RPA)', 'slug' => 'robotic-process-automation'],
            ['name' => 'Human Augmentation', 'slug' => 'human-augmentation'],
            ['name' => 'Edge AI & On-Device AI', 'slug' => 'edge-ai-on-device-ai']
        ];

        foreach ($categories as $category) {
            $entityManager->persist((new Category())->setName($category['name'])->setSlug($category['slug'])->setIcon('test'));
        }

        $entityManager->flush();
        return new Response("test");
    }
}
