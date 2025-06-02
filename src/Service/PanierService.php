<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class PanierService
{
    private $session;
    private $productRepository;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->session = $requestStack->getSession();
        $this->productRepository = $productRepository;
    }

    public function ajouter(int $id)
    {
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        $this->session->set('panier', $panier);
    }

    public function retirer(int $id)
    {
        $panier = $this->session->get('panier', []);
        unset($panier[$id]);
        $this->session->set('panier', $panier);
    }

    public function vider()
    {
        $this->session->remove('panier');
    }

    public function getContenu(): array
    {
        $panier = $this->session->get('panier', []);
        $panierComplet = [];

        foreach ($panier as $id => $quantite) {
            $produit = $this->productRepository->find($id);

            if ($produit) {
                $panierComplet[] = [
                    'produit' => $produit,
                    'quantite' => $quantite,
                    'total' => $produit->getPrice() * $quantite,
                ];
            }
        }

        return $panierComplet;
    }

    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getContenu() as $item) {
            $total += $item['total'];
        }

        return $total;
    }

    public function getPanierComplet(): array
    {
        $panier = $this->session->get('panier', []);
        $panierComplet = [];
    
        foreach ($panier as $id => $quantity) {
            $product = $this->productRepository->find($id);
    
            if ($product) {
                $panierComplet[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];
            }
        }

    return $panierComplet;
}

}
