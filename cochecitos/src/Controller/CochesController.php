<?php
namespace App\Controller;




use App\Entity\Coche;
use App\Entity\Marca;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CochesController extends AbstractController
{
    private $coches = [
        1 => ["nombre" => "Renault 5 Alpine", "escala" => "1/24", "color" => "naranja"],
        2 => ["nombre" => "Mustang Shelby GT500", "escala" => "1/18", "color" => "amarillo"],
        5 => ["nombre" => "Fiat 600D", "escala" => "1/32", "color" => "rojo"],
        7 => ["nombre" => "Porsche 911 Carrera", "escala" => "1/18", "color" => "negro"],
        9 => ["nombre" => "Citroen Xsara Picasso", "escala" => "1/24", "color" => "blanco"]
    ]; 

    #[Route('/coche/insertar', name:"insertar_coche")]
    public function insertar (ManagerRegistry $doctrine)
    {
        $entityManager=$doctrine->getManager();
        foreach($this-> coches as $c){
            $coche= new Coche();
            $coche-> setNombre($c["nombre"]);
            $coche-> setEscala($c["escala"]);
            $coche-> setColor($c["color"]);
            $entityManager->persist($coche);
        }
        try{
            $entityManager->flush();
            return new Response ("coches insertados");
        }
        catch (\Exception $e){
            return new Response ("error insertando objetos");

        }
    }
    #[Route('/coche/update/{id}/{nombre}', name:"modificar_coche")]
    public function update(ManagerRegistry $doctrine,$id,$nombre): Response{
        $entityManager=$doctrine->getManager();
        $repositorio=$doctrine->getRepository(Coche::class);
        $coche=$repositorio->find($id);
        if ($coche){
            $coche->setNombre($nombre);
            try
            {
                $entityManager->flush();
                return $this->render('ficha_contacto.html.twig',['coche'=>$coche
    
            ]);
            }
            catch(\Exception $e){
                return new Response("error insertando objetos");
            }
        }
        else
        {
            return $this->render('ficha_coche.html.twig',['coche'=>null]);
        }
    }

    #[Route('/coche/delete/{id}', name:"eliminar_coche")]
    public function delete(ManagerRegistry $doctrine,$id): Response{
        $entityManager=$doctrine->getManager();
        $repositorio=$doctrine->getRepository(Coche::class);
        $coche=$repositorio->find($id);
        if ($coche){
            try
            {
                $entityManager->remove($coche);
                $entityManager->flush();
                return new Response("coche eliminado");
            }
            catch(\Exception $e){
                return new Response("error eliminando objeto");
            }
        }
        else
        {
            return $this->render('ficha_coche.html.twig',['coche'=>null]);
        }
    }

    
    #[Route('/coche/{codigo<\d+>?1}', name:"ficha_coche")]
    
    public function ficha(ManagerRegistry $doctrine,$codigo): Response{

        $repositorio=$doctrine->getRepository(Coche::class);
        $coche=$repositorio->find($codigo);

        return $this->render('ficha_coche.html.twig',['contacto'=>$coche
    
        ]);
    
    }
    
    
    #[Route('/coche/buscar/{texto}', name: 'buscar_coche')]
    public function buscar(ManagerRegistry $doctrine, $texto ):Response
        {
            $repositorio=$doctrine->getRepository(Coche::class);
            $coches=$repositorio->findByName($texto);
            return $this -> render ('lista_coches.html.twig', [
                'coches' => $coches
            ]);
   
}
    #[Route('/coche/insertarConMarca', name: 'insertar_con_marca_coche')]

    public function insertarConCoche(ManagerRegistry $doctrine): Response{
        $entityManager=$doctrine->getManager();
        $marca=new Marca();

        $marca->setNombre("Ferrari");
        $coche=new Coche();

        $coche->setNombre("Ferrari 458 Italia");
        $coche->setEscala("1/24");
        $coche->setColor("rojo");
        $coche->setMarca($marca);

        $entityManager->persist($marca);
        $entityManager->persist($coche);

        $entityManager->flush();
        return $this->render('ficha_coche.html.twig',[
            'coche'=>$coche
        ]);


    }

    #[Route('/coche/insertarSinMarca', name: 'insertar_sin_marca_coche')]

    public function insertarSinMarca(ManagerRegistry $doctrine): Response{
        $entityManager=$doctrine->getManager();
        $repositorio=$doctrine->getRepository(Marca::class);
        

        $marca=$repositorio->findOneBy(["nombre"=>"Francia"]);

        $coche=new Coche();

        $coche->setNombre("Inserción de prueba con marca");
        $coche->setEscala("1/50");
        $coche->setColor("Marron");
        $coche->setMArca($marca);

       
        $entityManager->persist($coche);

        $entityManager->flush();
        return $this->render('ficha_coche.html.twig',[
            'coche'=>$coche
        ]);


    }
}