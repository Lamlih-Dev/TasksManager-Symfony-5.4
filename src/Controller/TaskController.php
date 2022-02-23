<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\TaskRepository;
use App\Entity\Task;
use App\Form\Type\TaskType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class TaskController extends AbstractController
{
    private $taskRepository;
    private $flashMessage;

    public function __construct(TaskRepository $taskRepository, FlashBagInterface $flashMessage){
        $this->taskRepository = $taskRepository;
        $this->flashMessage = $flashMessage;
    }

    public function index() 
    {
        return $this->render("task/home.html.twig");
    }

    /**
    * @Route("/task/create")
    */
    public function createTask(Request $request, ManagerRegistry $doctrine) : Response
    {
        $task = new Task();

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $task = $form->getData();
            $entityManager =  $doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
            $this->flashMessage->add("message","Task was added successfuly !");

            return $this->redirectToRoute("allTasks");
        }

        return $this->render('task/addTask.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
    * @Route("/task/allTasks", name="allTasks")
    */
    public function allTasks()
    {
        $allTasks = $this->taskRepository->findAll();
        return $this->render("task/allTasks.html.twig",["tasks" => $allTasks]);
    }

    /**
    * @Route("/task/{id}", name="task_show")
    */
    public function showTask($id)
    {
        $task = $this->taskRepository->find($id);
        return $this->render("task/showTask.html.twig",["task" => $task]);
    }

    /**
    * @Route("/task/modify/{id}")
    */
    public function modify($id,Request $request, ManagerRegistry $doctrine)
    {
        $task = $this->taskRepository->find($id);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $task = $form->getData();
            $entityManager =  $doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
            $this->flashMessage->add("message","Task was updated successfuly !");

            return $this->redirectToRoute("allTasks");
        }

        return $this->render("task/modifyTask.html.twig",[
            'form' => $form->createView(),
        ]);
    }

    /**
    * @Route("/task/delete/{id}")
    */
    public function delete($id, ManagerRegistry $doctrine) : Response
    {
        $entityManager = $doctrine->getManager();
        $repository = $doctrine->getRepository(Task::class);
        $task = $repository->find($id);
        if($task != null){
            $entityManager->remove($task);
            $entityManager->flush();
            $this->flashMessage->add("message","Task was deleted successfuly !");
        }
        return $this->redirectToRoute("allTasks"); 
    }          
}