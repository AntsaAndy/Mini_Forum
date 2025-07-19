<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\Post;
use App\Form\TopicType;
use App\Repository\TopicRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum')]
class ForumController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TopicRepository $topicRepository,
        private PostRepository $postRepository
    ) {}

    #[Route('/', name: 'forum_index')]
    public function index(): Response
    {
        $topics = $this->topicRepository->findAllWithPostsCount();

        return $this->render('forum/index.html.twig', [
            'topics' => $topics,
        ]);
    }

    #[Route('/topic/{id}', name: 'forum_topic_show', requirements: ['id' => '\d+'])]
    public function showTopic(Topic $topic): Response
    {
        $posts = $this->postRepository->findByTopicOrderedByDate($topic);

        return $this->render('forum/topic.html.twig', [
            'topic' => $topic,
            'posts' => $posts,
        ]);
    }

    #[Route('/topic/new', name: 'forum_topic_new')]
    public function newTopic(Request $request): Response
    {
        // Optionnel : tu peux ici créer un formulaire Symfony complet pour le topic + premier post

        if ($request->isMethod('POST')) {
            $topic = new Topic();
            $topic->setTitle($request->request->get('title'));
            $topic->setDescription($request->request->get('description'));
            $topic->setCategory($request->request->get('category'));
            $topic->setAuthor($this->getUser());

            $this->entityManager->persist($topic);

            // Premier post du topic
            $post = new Post();
            $post->setContent($request->request->get('content'));
            $post->setTopic($topic);
            $post->setAuthor($this->getUser());

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            $this->addFlash('success', 'Sujet créé avec succès !');

            return $this->redirectToRoute('forum_topic_show', ['id' => $topic->getId()]);
        }

        return $this->render('forum/new_topic.html.twig');
    }

    #[Route('/topic/{id}/edit', name: 'forum_topic_edit', requirements: ['id' => '\d+'])]
    public function editTopic(Request $request, Topic $topic): Response
    {
        // Vérification : seul l’auteur peut modifier
        if ($topic->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres sujets.');
        }

        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Sujet modifié avec succès !');

            return $this->redirectToRoute('forum_topic_show', ['id' => $topic->getId()]);
        }

        return $this->render('forum/edit_topic.html.twig', [
            'topic' => $topic,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/topic/{id}/delete', name: 'forum_topic_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteTopic(Request $request, Topic $topic): Response
    {
        if ($topic->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres sujets.');
        }

        if ($this->isCsrfTokenValid('delete-topic'.$topic->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($topic);
            $this->entityManager->flush();

            $this->addFlash('success', 'Sujet supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('forum_index');
    }

    #[Route('/topic/{id}/reply', name: 'forum_topic_reply', requirements: ['id' => '\d+'])]
    public function replyToTopic(Topic $topic, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $post = new Post();
            $post->setContent($request->request->get('content'));
            $post->setTopic($topic);
            $post->setAuthor($this->getUser());

            $topic->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            $this->addFlash('success', 'Réponse ajoutée avec succès !');
        }

        return $this->redirectToRoute('forum_topic_show', ['id' => $topic->getId()]);
    }

    #[Route('/post/{id}/edit', name: 'forum_post_edit', requirements: ['id' => '\d+'])]
    public function editPost(Post $post, Request $request): Response
    {
        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres messages.');
        }

        if ($request->isMethod('POST')) {
            $post->setContent($request->request->get('content'));
            $post->setUpdatedAt(new \DateTimeImmutable());
            $post->setIsEdited(true);

            $this->entityManager->flush();

            $this->addFlash('success', 'Message modifié avec succès !');

            return $this->redirectToRoute('forum_topic_show', ['id' => $post->getTopic()->getId()]);
        }

        return $this->render('forum/edit_post.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/post/{id}/delete', name: 'forum_post_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deletePost(Request $request, Post $post): Response
    {
        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres messages.');
        }

        if ($this->isCsrfTokenValid('delete-post'.$post->getId(), $request->request->get('_token'))) {
            $topicId = $post->getTopic()->getId();

            $this->entityManager->remove($post);
            $this->entityManager->flush();

            $this->addFlash('success', 'Message supprimé avec succès !');

            return $this->redirectToRoute('forum_topic_show', ['id' => $topicId]);
        }

        $this->addFlash('error', 'Jeton CSRF invalide.');

        return $this->redirectToRoute('forum_index');
    }

    #[Route('/post/{id}/like', name: 'forum_post_like', requirements: ['id' => '\d+'])]
    public function likePost(Post $post): Response
    {
        $post->incrementLikes();
        $this->entityManager->flush();

        return $this->json(['likes' => $post->getLikes()]);
    }
}
