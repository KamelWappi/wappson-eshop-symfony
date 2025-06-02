<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;




#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('', name: 'panier_index')]
    public function index(PanierService $panierService): Response
    {
        return $this->render('panier/index.html.twig', [
            'items' => $panierService->getContenu(),
            'total' => $panierService->getTotal(),
        ]);
    }

    #[Route('/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouter(int $id, PanierService $panierService): Response
    {
        $panierService->ajouter($id);
        return $this->redirectToRoute('app_product');
    }

    #[Route('/retirer/{id}', name: 'panier_retirer')]
    public function retirer(int $id, PanierService $panierService): Response
    {
        $panierService->retirer($id);
        return $this->redirectToRoute('panier_index');
    }

    #[Route('/vider', name: 'panier_vider')]
    public function vider(PanierService $panierService): Response
    {
        $panierService->vider();
        return $this->redirectToRoute('panier_index');
    }

    #[Route('/valider', name: 'panier_valider')]
    public function valider(
        PanierService $panierService,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $panier = $panierService->getPanierComplet();
    
        if (empty($panier)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_product');
        }
    
        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $total = 0;
    
        foreach ($panier as $ligne) {
            $product = $ligne['product'];
            $quantity = is_array($ligne['quantity']) ? $ligne['quantity'][0] : $ligne['quantity'];
    
            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setPrice($product->getPrice());
            $orderItem->setOrder($order);
    
            $entityManager->persist($orderItem);
            $total += $product->getPrice() * $quantity;
        }
    
        $order->setTotal($total);
        $entityManager->persist($order);
        $entityManager->flush();
    
        $panierService->vider();
    
        // Email de confirmation 
        // je l'ai simulé avec Mailtrap.oi aussi
        $email = (new Email())
            ->from('wappson-eshop@gmail.com')
            ->to('wilfriedwappi2@gmail.com')
            ->subject('Confirmation de commande')
            ->text("Merci pour votre commande !\nTotal : " . $order->getTotal() . " €")
            ->html('<p>Merci pour votre commande !</p><p>Total : <strong>' . $order->getTotal() . ' €</strong></p>');
    
        $mailer->send($email);
    
        $this->addFlash('success', 'Votre commande a été effectuée et un email a été envoyé.');
    
        return $this->redirectToRoute('app_product');
    }
    
}
