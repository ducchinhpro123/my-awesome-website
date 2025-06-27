<?php

namespace MyAwesomeWebsite\model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'carts')]
class Cart
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'cart', targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartItem::class, cascade: ['persist', 'remove'])]
    private Collection $cartItems;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->cartItems = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function setCartItems(Collection $cartItems)
    {
        $this->cartItems = $cartItems;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getCartItems()
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem)
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems[] = $cartItem;
            $cartItem->setCart($this);
        }
    }

}
?>
