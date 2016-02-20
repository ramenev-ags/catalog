<?php
/**
 * Created by PhpStorm.
 * User: eXPert
 * Date: 16.02.2016
 * Time: 22:29
 */

namespace CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Firm
 *
 * @ORM\Entity(repositoryClass="CatalogBundle\Repository\FirmRepository")
 * @ORM\Table(name="firms")
 * @DoctrineAssert\UniqueEntity("name")
 *
 * @author Dmitriy Ramenev <diman4k@gmail.com>
 */
class Firm
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var String
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     */
    protected $name;

    /**
     * @var Building
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Building")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="building_id", referencedColumnName="id", nullable=false)
     * })
     *
     * @JMS\SerializedName("address")
     * @JMS\Type("string")
     */
    protected $building;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Phone", mappedBy="firm")
     * @JMS\Type("array<string>")
     */
    protected $phones;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Rubric", inversedBy="firms", cascade={"persist","remove"})
     * @ORM\JoinTable(name="firms_rubrics")
     * @JMS\Type("array<string>")
     */
    protected $rubrics;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->phones = new ArrayCollection();
        $this->rubrics = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Firm
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set building
     *
     * @param Building $building
     *
     * @return Firm
     */
    public function setBuilding(Building $building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * Get building
     *
     * @return Building
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * Add phone
     *
     * @param Phone $phone
     *
     * @return Firm
     */
    public function addPhone(Phone $phone)
    {
        $this->phones[] = $phone;

        return $this;
    }

    /**
     * Remove phone
     *
     * @param Phone $phone
     */
    public function removePhone(Phone $phone)
    {
        $this->phones->removeElement($phone);
    }

    /**
     * Get phones
     *
     * @return Collection
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * Add rubric
     *
     * @param Rubric $rubric
     *
     * @return Firm
     */
    public function addRubric(Rubric $rubric)
    {
        $this->rubrics[] = $rubric;

        return $this;
    }

    /**
     * Remove rubric
     *
     * @param Rubric $rubric
     */
    public function removeRubric(Rubric $rubric)
    {
        $this->rubrics->removeElement($rubric);
    }

    /**
     * Get rubrics
     *
     * @return Collection
     */
    public function getRubrics()
    {
        return $this->rubrics;
    }

    /**
     * @JMS\VirtualProperty()
     * @return string
     */
    public function getLat()
    {
        return $this->getBuilding()->getLat();
    }

    /**
     * @JMS\VirtualProperty()
     * @return string
     */
    public function getLng()
    {
        return $this->getBuilding()->getLng();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
