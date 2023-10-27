<?php
namespace App\Controller;





use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Marca;
use App\Entity\Coche;
use App\Entity\User;
use App\Form\CocheType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\String\Slugger\SluggerInterface;

class CochesController extends AbstractController
{
    private $coches = [
        1 => ["nombre" => "Renault 5 Alpine", "escala" => "1/24", "color" => "naranja"],
        2 => ["nombre" => "Mustang Shelby GT500", "escala" => "1/18", "color" => "amarillo"],
        5 => ["nombre" => "Fiat 600D", "escala" => "1/32", "color" => "rojo"],
        7 => ["nombre" => "Porsche 911 Carrera", "escala" => "1/18", "color" => "negro"],
        9 => ["nombre" => "Citroen Xsara Picasso", "escala" => "1/24", "color" => "blanco"]
    ];
    
    #[Route('/coche/nuevo', name:"nuevo_coches")]
    public function nuevo(ManagerRegistry $doctrine, Request $request){
        $coche=new Coche();

        $formulario = $this->createForm(ContactoType::class, $coche);

            $formulario->handleRequest($request);

            if($formulario->isSubmitted()&& $formulario->isValid()){
                $coche=$formulario->getData();
                $entityManager=$doctrine->getManager();
                $entityManager->persist($coche);
                $entityManager->flush();
                return $this->redirectToRoute('ficha_coche', ["codigo"=>$coche->getId()]);

            }

        return $this->render('coches/nuevo.html.twig',array(
            'formulario'=>$formulario->createView()
        ));
    }
    #[Route('/coche/editar/{codigo}', name:"editar_coche", requirements:["codigo"=>"\d+"])]
    public function editar(ManagerRegistry $doctrine, Request $request, $codigo, SessionInterface $session, SluggerInterface $slugger){
        $user=$this->getUser();

        if ($user){
        $repositorio=$doctrine->getRepository(Coche::class);
        $coche=$repositorio->find($codigo);

        $formulario = $this->createForm(CocheType::class, $coche);

            $formulario->handleRequest($request);

            if($formulario->isSubmitted()&& $formulario->isValid()){
                $coche=$formulario->getData();
                $file = $formulario->get('file')->getData();
                if ($file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                    // Move the file to the directory where images are stored
                    try {

                        $file->move(
                            $this->getParameter('images_directory'), $newFilename
                        );                      
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                        return new Response($e->getMessage());
                    }

                    // updates the 'file$filename' property to store the PDF file name
                    // instead of its contents
                    $coche->setFile($newFilename);
                }
                $entityManager=$doctrine->getManager();
                $entityManager->persist($coche);
                $entityManager->flush();
               

            }
            

        return $this->render('coches/nuevo.html.twig',array(
            'formulario'=>$formulario->createView()
        ));
    }
    else{
        $url= $this->generateUrl(
            'editar_coche',['codigo'=>$codigo]
        );
        $session->set('enlace', $url);
        return $this->redirectToRoute('app_login');
    }
    }

    #[Route('/coche/delete/{id}', name:"eliminar_coche")]
    public function delete(ManagerRegistry $doctrine,$id, SessionInterface $session): Response{
        $user=$this->getUser();


        if ($user){
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

        else{
            $url= $this->generateUrl(
                'eliminar_coche',['id'=>$id]
            );
            $session->set('enlace', $url);
            return $this->redirectToRoute('app_login');
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