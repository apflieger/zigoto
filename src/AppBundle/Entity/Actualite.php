<?php


namespace AppBundle\Entity;


class Actualite implements PersistableInterface
{
    use Persistable;

    /** @var string */
    private $contenu;

    /**
     * @param string $contenu
     */
    public function __construct($contenu)
    {
        $this->contenu = $contenu;
    }

    /**
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }
}