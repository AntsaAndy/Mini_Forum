<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\Post;
use App\Repository\TopicRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum')]
class ForumController extends AbstractController
{public function __construct(
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
        if ($request->isMethod('POST')) {
            $topic = new Topic();
            $topic->setTitle($request->request->get('title'));
            $topic->setDescription($request->request->get('description'));
            $topic->setCategory($request->request->get('category'));
            $topic->setAuthor($this->getUser());

            $this->entityManager->persist($topic);

            // Créer le premier post
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
        // Vérifier que l'utilisateur peut modifier ce post
        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
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

    #[Route('/post/{id}/delete', name: 'forum_post_delete', requirements: ['id' => '\d+'])]
    public function deletePost(Post $post): Response
    {
        // Vérifier que l'utilisateur peut supprimer ce post
        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $topicId = $post->getTopic()->getId();
        
        $this->entityManager->remove($post);
        $this->entityManager->flush();

        $this->addFlash('success', 'Message supprimé avec succès !');
        return $this->redirectToRoute('forum_topic_show', ['id' => $topicId]);
    }

    #[Route('/post/{id}/like', name: 'forum_post_like', requirements: ['id' => '\d+'])]
    public function likePost(Post $post): Response
    {
        $post->incrementLikes();
        $this->entityManager->flush();

        return $this->json(['likes' => $post->getLikes()]);
    }
}