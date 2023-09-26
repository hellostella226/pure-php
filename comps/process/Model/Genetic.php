<?
namespace Model;

class Genetic extends Base
{
    public ?object $conn = null;

    function __construct()
    {
        $this->conn = (new PDOFactory)->PDOCreate();
    }
}