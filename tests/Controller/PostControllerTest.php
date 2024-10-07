<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Rubrik;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PostControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Post::class);

        // Очищаем репозиторий перед началом каждого теста
        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();
    }

    private function createUser(): User
    {
        // Метод для создания и сохранения фиктивного пользователя
        $user = new User();
        $user->setEmail('testuser' . uniqid() . '@example.com'); // Уникальный email
        $user->setPassword('securepassword'); // Обычно пароли нужно хешировать
        $user->setPsuedo('testuser');
        $user->setCity('Paris');
        $user->setCountry('France');
        $user->setOpption('yes');
        $user->setRoles(['ROLE_SUPER_ADMIN']);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    private function loginUser(User $user): void
    {
        // Логиним пользователя
        $this->client->loginUser($user);
    }

    public function testIndex(): void
    {
        $user = $this->createUser();
        $this->loginUser($user); // Логиним пользователя

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/admin/?entity=Post&action=list');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Post');
    }

        public function testNew(): void
    {
        $user = $this->createUser();
        $this->loginUser($user); // Логиним пользователя

        // Создаем рубрику
        $rubrik = new Rubrik();
        $rubrik->setName('CPU');
        $this->manager->persist($rubrik);
        $this->manager->flush(); // Сначала сохраняем рубрику

        $this->client->request('GET', '/admin/?entity=Post&action=new');

        self::assertResponseStatusCodeSame(200);

        // Отправляем форму добавления нового поста
        $this->client->submitForm('Create', [
            'Post[title]' => 'Test Title',
            'Post[subtitle]' => 'Test subTitle', // Добавлено поле subtitle
            'Post[content]' => 'This is a test content.',
            'Post[content1]' => 'This is a test content1.',
            'Post[photo]' => 'testphoto.jpg',
            'Post[isPublished]' => true,
            'Post[author]' => $user->getId(), // Устанавливаем автора поста
            'Post[rubrik]' => $rubrik->getId(), // Устанавливаем рубрику
        ]);

        // Проверяем, что после создания происходит редирект на список постов
        self::assertResponseRedirects('/admin/?entity=Post&action=list');

        // Проверяем, что количество постов в репозитории стало 1
        self::assertSame(1, $this->repository->count([]));

        // Проверяем, что созданный пост имеет правильную рубрику
        $post = $this->repository->findOneBy(['title' => 'Test Title']);
        self::assertSame($rubrik->getId(), $post->getRubrik()->getId());
    }

    public function testShow(): void
    {
        $user = $this->createUser();
        $this->loginUser($user); // Логиним пользователя

        $rubrik = new Rubrik();
        $rubrik->setName('CPU');
        $this->manager->persist($rubrik);
        $this->manager->flush(); // Сначала сохраняем рубрику

        // Создаем пост
        $fixture = new Post();
        $fixture->setTitle('My Test Title');
        $fixture->setContent('This is the content of the post.');
        $fixture->setContent1('Additional content.');
        $fixture->setPhoto('testphoto.jpg');
        $fixture->setCreatedAt(new \DateTimeImmutable());
        $fixture->setIsPublished(true);
        $fixture->setUser($user); // Устанавливаем автора
        $fixture->setRubrik($rubrik); // Устанавливаем рубрику

        $this->manager->persist($fixture);
        $this->manager->flush();

        // Переходим на страницу просмотра поста
        $this->client->request('GET', sprintf('/admin/?entity=Post&action=show&id=%s', $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Post');
    }

    public function testEdit(): void
    {
        $user = $this->createUser();
        $this->loginUser($user); // Логиним пользователя

        $rubrik = new Rubrik();
        $rubrik->setName('CPU');
        $this->manager->persist($rubrik);
        $this->manager->flush(); // Сначала сохраняем рубрику

        // Создаем пост
        $fixture = new Post();
        $fixture->setTitle('Initial Title');
        $fixture->setContent('Initial content.');
        $fixture->setContent1('Initial content1.');
        $fixture->setPhoto('initialphoto.jpg');
        $fixture->setCreatedAt(new \DateTimeImmutable());
        $fixture->setIsPublished(true);
        $fixture->setUser($user); // Устанавливаем автора
        $fixture->setRubrik($rubrik);

        $this->manager->persist($fixture);
        $this->manager->flush();

        // Переходим на страницу редактирования поста
        $this->client->request('GET', sprintf('/admin/?entity=Post&action=edit&id=%s', $fixture->getId()));

        // Отправляем форму с обновленными данными
        $this->client->submitForm('Save changes', [
            'Post[title]' => 'Updated Title',
            'Post[content]' => 'Updated content.',
        ]);

        // Проверяем редирект после обновления
        self::assertResponseRedirects('/admin/?entity=Post&action=list');

        // Обновляем данные из базы и проверяем, что они были обновлены
        $fixture = $this->repository->find($fixture->getId());

        self::assertSame('Updated Title', $fixture->getTitle());
        self::assertSame('Updated content.', $fixture->getContent());
    }

    public function testRemove(): void
    {
        $user = $this->createUser();
        $this->loginUser($user); // Логиним пользователя

        $rubrik = new Rubrik();
        $rubrik->setName('CPU');
        $this->manager->persist($rubrik);
        $this->manager->flush(); // Сначала сохраняем рубрику

        // Создаем пост
        $fixture = new Post();
        $fixture->setTitle('Title to be deleted');
        $fixture->setContent('Content to be deleted.');
        $fixture->setContent1('Some content1.');
        $fixture->setPhoto('tobedeletedphoto.jpg');
        $fixture->setCreatedAt(new \DateTimeImmutable());
        $fixture->setIsPublished(true);
        $fixture->setUser($user); // Устанавливаем автора
        $fixture->setRubrik($rubrik); // Устанавливаем автор

        $this->manager->persist($fixture);
        $this->manager->flush();

        // Переходим на страницу удаления поста
        $this->client->request('GET', sprintf('/admin/?entity=Post&action=delete&id=%s', $fixture->getId()));

        // Проверяем редирект после удаления
        self::assertResponseRedirects('/admin/?entity=Post&action=list');

        // Проверяем, что пост удален из базы данных
        self::assertSame(0, $this->repository->count([]));
    }
}
