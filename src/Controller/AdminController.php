<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(UserRepository $userRepository, Request $request): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/action', name: 'admin_user_action', methods: ['POST'])]
    public function userAction(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager,
        TokenStorageInterface $tokenStorage
    ): Response
    {
        $token = new CsrfToken('admin_user_action', $request->request->get('_csrf_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            $this->addFlash('danger', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $action = $request->request->get('action');

        $selectedUsers = $request->request->all('selected_users');
        if (!is_array($selectedUsers)) {
            $selectedUsers = [$selectedUsers];
        }

        if (empty($selectedUsers)) {
            $this->addFlash('warning', 'No users selected.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $users = $userRepository->findBy(['id' => $selectedUsers]);

        if (empty($users)) {
            $this->addFlash('warning', 'Selected users not found.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $currentUser = $this->getUser();
        $selfBlocked = false;
        $selfDeleted = false;
        $usernames = [];

        foreach ($users as $user) {
            $usernames[] = '<strong>' . htmlspecialchars($user->getName()) . '</strong>';
            switch ($action) {
                case 'block':
                    $user->setIsBlocked(true);
                    if ($user === $currentUser) {
                        $selfBlocked = true;
                    }
                    break;
                case 'unblock':
                    $user->setIsBlocked(false);
                    break;
                case 'delete':
                    if ($user === $currentUser) {
                        $selfDeleted = true;
                    }
                    $em->remove($user);
                    break;
                default:
                    $this->addFlash('danger', 'Invalid action.');
                    return $this->redirectToRoute('admin_dashboard');
            }
        }

        $em->flush();

        if ($selfDeleted || $selfBlocked) {

            $tokenStorage->setToken(null);

            $request->getSession()->invalidate();
            $this->addFlash('warning', 'Your account has been blocked or deleted. You have been logged out.');
            return $this->redirectToRoute('app_login');
        }

        $names = $this->formatUsernames($usernames);

       switch ($action) {
            case 'block':
                $this->addFlash('danger', sprintf('%s %s been blocked.',
                    $names,
                    count($usernames) === 1 ? 'has' : 'have'
                ));
                break;
            case 'unblock':
                $this->addFlash('success', sprintf('%s %s been unblocked.',
                    $names,
                    count($usernames) === 1 ? 'has' : 'have'
                ));
                break;
            case 'delete':
                $this->addFlash('secondary', sprintf('%s %s been deleted.',
                    $names,
                    count($usernames) === 1 ? 'has' : 'have'
                ));
                break;
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/user/delete-test/{id}', name: 'admin_user_delete_test')]
    public function deleteTest(int $id, UserRepository $repo, EntityManagerInterface $em): Response
    {
        $user = $repo->find($id);
        if (!$user) {
            return new Response('User not found');
        }

        $em->remove($user);
        $em->flush();

        return new Response('User deleted');
    }
    
    private function formatUsernames(array $usernames): string
    {
        $count = count($usernames);
        if ($count === 0) {
            return '';
        } elseif ($count === 1) {
            return $usernames[0];
        } elseif ($count === 2) {
            return $usernames[0] . ' and ' . $usernames[1];
        }

        $last = array_pop($usernames);
        return implode(', ', $usernames) . ' and ' . $last;
    }
}