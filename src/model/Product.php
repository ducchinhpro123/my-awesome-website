<?php

namespace MyAwesomeWebsite\model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 10, nullable: true, scale: 2)]
    private ?string $price;

    #[ORM\Column(type: 'string', nullable: true, name: 'image_url')]
    private ?string $imageUrl;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $rating = 0;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true)]
    private Category $category;

    public function __toString()
    {
        return "\nproduct_id: " . $this->id . "\ndescription: " . $this->description;
    }

    public function __construct(string $name, string $price, Category $category, $rating)
    {
        $this->name = $name;
        $this->price = $price;
        $this->category = $category;
        $this->rating = $rating;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    public function getPrice()
    {
        return number_format($this->price, 0, '.', ',') . '₫';
    }

    public function setPrice(?string $price)
    {
        $this->price = $price;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

}

?>
