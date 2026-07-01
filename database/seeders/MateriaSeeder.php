<?php

namespace Database\Seeders;

use App\Models\Materia;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        $materias = [
            /*
            // --- DESARROLLO DE SOFTWARE (TS-DS) ---
            // Semestre 1
            ['nombre' => 'Introducción a la Programación', 'codigo' => 'DS-101', 'descripcion' => 'Fundamentos de algoritmos y lógica de programación.'],
            ['nombre' => 'Matemáticas Discretas', 'codigo' => 'DS-102', 'descripcion' => 'Lógica matemática, conjuntos, relaciones y grafos.'],
            ['nombre' => 'Sistemas Operativos', 'codigo' => 'DS-103', 'descripcion' => 'Introducción al funcionamiento interno de los SO y línea de comandos.'],
            ['nombre' => 'Comunicación y Redacción', 'codigo' => 'DS-104', 'descripcion' => 'Habilidades comunicativas y redacción de informes técnicos.'],
            // Semestre 2
            ['nombre' => 'Programación I', 'codigo' => 'DS-201', 'descripcion' => 'Programación orientada a objetos (POO) básica.'],
            ['nombre' => 'Estructuras de Datos', 'codigo' => 'DS-202', 'descripcion' => 'Listas, pilas, colas, árboles y grafos.'],
            ['nombre' => 'Base de Datos I', 'codigo' => 'DS-203', 'descripcion' => 'Diseño lógico y físico de bases de datos relacionales y SQL.'],
            ['nombre' => 'Arquitectura de Computadoras', 'codigo' => 'DS-204', 'descripcion' => 'Funcionamiento interno del hardware y ensamble de equipos.'],
            // Semestre 3
            ['nombre' => 'Programación II', 'codigo' => 'DS-301', 'descripcion' => 'Desarrollo de aplicaciones web y de escritorio multi-capas.'],
            ['nombre' => 'Base de Datos II', 'codigo' => 'DS-302', 'descripcion' => 'Triggers, procedimientos almacenados y optimización de consultas.'],
            ['nombre' => 'Ingeniería de Software I', 'codigo' => 'DS-303', 'descripcion' => 'Ciclos de vida del software, UML y metodologías de desarrollo.'],
            ['nombre' => 'Redes de Datos', 'codigo' => 'DS-304', 'descripcion' => 'Conceptos básicos del modelo OSI, TCP/IP y direccionamiento.'],
            // Semestre 4
            ['nombre' => 'Desarrollo Web', 'codigo' => 'DS-401', 'descripcion' => 'Desarrollo de aplicaciones Frontend con HTML, CSS y Javascript.'],
            ['nombre' => 'Ingeniería de Software II', 'codigo' => 'DS-402', 'descripcion' => 'Metodologías ágiles (Scrum/Kanban) y aseguramiento de calidad.'],
            ['nombre' => 'Análisis y Diseño de Sistemas', 'codigo' => 'DS-403', 'descripcion' => 'Análisis de requerimientos y modelado de sistemas de información.'],
            ['nombre' => 'Seguridad Informática', 'codigo' => 'DS-404', 'descripcion' => 'Fundamentos de criptografía y seguridad en el desarrollo.'],
            // Semestre 5
            ['nombre' => 'Desarrollo Móvil', 'codigo' => 'DS-501', 'descripcion' => 'Desarrollo de aplicaciones para Android e iOS.'],
            ['nombre' => 'Gestión de Proyectos TI', 'codigo' => 'DS-502', 'descripcion' => 'Planeación, ejecución y control de proyectos tecnológicos.'],
            ['nombre' => 'Inteligencia Artificial Aplicada', 'codigo' => 'DS-503', 'descripcion' => 'Conceptos básicos de Machine Learning y automatización.'],
            ['nombre' => 'Ética Profesional', 'codigo' => 'DS-504', 'descripcion' => 'Responsabilidad social y comportamiento ético en TI.'],
            // Semestre 6
            ['nombre' => 'Taller de Proyecto Final', 'codigo' => 'DS-601', 'descripcion' => 'Desarrollo e integración de un proyecto de software final.'],
            ['nombre' => 'Calidad de Software', 'codigo' => 'DS-602', 'descripcion' => 'Pruebas de software unitarias, integración y sistemas.'],
            ['nombre' => 'Emprendimiento Tecnológico', 'codigo' => 'DS-603', 'descripcion' => 'Modelos de negocio y creación de Startups.'],
            ['nombre' => 'Práctica Profesional', 'codigo' => 'DS-604', 'descripcion' => 'Práctica supervisada en una empresa del sector.'],

            // --- REDES Y TELECOMUNICACIONES (TS-RT) ---
            // Semestre 1
            ['nombre' => 'Fundamentos de Redes', 'codigo' => 'RT-101', 'descripcion' => 'Conceptos básicos de redes cableadas e inalámbricas.'],
            ['nombre' => 'Arquitectura del Computador', 'codigo' => 'RT-102', 'descripcion' => 'Componentes físicos y configuración de computadoras.'],
            ['nombre' => 'Introducción a la Electrónica', 'codigo' => 'RT-103', 'descripcion' => 'Principios de circuitos eléctricos y magnitudes electrónicas.'],
            ['nombre' => 'Introducción a la Programación (Redes)', 'codigo' => 'RT-104', 'descripcion' => 'Fundamentos de lógica y scripting para redes.'],
            // Semestre 2
            ['nombre' => 'Enrutamiento y Conmutación', 'codigo' => 'RT-201', 'descripcion' => 'Configuración de switches and routers Cisco.'],
            ['nombre' => 'Redes Inalámbricas', 'codigo' => 'RT-202', 'descripcion' => 'Configuración y mantenimiento de tecnologías WiFi y radio enlaces.'],
            ['nombre' => 'Sistemas Operativos de Red', 'codigo' => 'RT-203', 'descripcion' => 'Instalación y configuración de Linux y Windows Server.'],
            ['nombre' => 'Cableado Estructurado', 'codigo' => 'RT-204', 'descripcion' => 'Estándares y tendido de cable UTP, STP y canalización.'],
            // Semestre 3
            ['nombre' => 'Telefonía IP', 'codigo' => 'RT-301', 'descripcion' => 'Configuración de centrales VoIP y telefonía digital.'],
            ['nombre' => 'Seguridad en Redes', 'codigo' => 'RT-302', 'descripcion' => 'Configuración de Firewalls, VPNs y políticas de seguridad.'],
            ['nombre' => 'Administración de Servidores', 'codigo' => 'RT-303', 'descripcion' => 'Servicios de red: DNS, DHCP, Web, FTP y Correo.'],
            ['nombre' => 'Fibra Óptica', 'codigo' => 'RT-304', 'descripcion' => 'Fusión y tendido de enlaces de fibra óptica.'],
            // Semestre 4
            ['nombre' => 'Proyecto de Redes', 'codigo' => 'RT-401', 'descripcion' => 'Diseño e integración de infraestructura de red completa.'],
            ['nombre' => 'Servicios Cloud', 'codigo' => 'RT-402', 'descripcion' => 'Fundamentos de AWS, Azure e infraestructura como servicio.'],
            ['nombre' => 'Soporte Técnico y Helpdesk', 'codigo' => 'RT-403', 'descripcion' => 'Atención al usuario y metodologías ITIL.'],
            ['nombre' => 'Legislación Informática', 'codigo' => 'RT-404', 'descripcion' => 'Leyes de telecomunicaciones, derechos de autor y delitos informáticos.'],

            // --- DISEÑO GRÁFICO (TM-DG) ---
            // Semestre 1
            ['nombre' => 'Fundamentos del Diseño', 'codigo' => 'DG-101', 'descripcion' => 'Elementos básicos visuales: punto, línea, color y composición.'],
            ['nombre' => 'Dibujo Artístico', 'codigo' => 'DG-102', 'descripcion' => 'Técnicas de bocetado a mano alzada y perspectiva.'],
            ['nombre' => 'Historia del Arte', 'codigo' => 'DG-103', 'descripcion' => 'Evolución de las corrientes artísticas y su influencia moderna.'],
            ['nombre' => 'Herramientas Digitales I', 'codigo' => 'DG-104', 'descripcion' => 'Uso avanzado de Adobe Photoshop para retoque fotográfico.'],
            // Semestre 2
            ['nombre' => 'Tipografía', 'codigo' => 'DG-201', 'descripcion' => 'Uso expresivo y legible del texto y familias tipográficas.'],
            ['nombre' => 'Ilustración Vectorial', 'codigo' => 'DG-202', 'descripcion' => 'Ilustración digital con Adobe Illustrator.'],
            ['nombre' => 'Fotografía Digital', 'codigo' => 'DG-203', 'descripcion' => 'Manejo de cámara réflex, composición e iluminación.'],
            ['nombre' => 'Herramientas Digitales II', 'codigo' => 'DG-204', 'descripcion' => 'Técnicas avanzadas de pintura digital y fotomontaje.'],
            // Semestre 3
            ['nombre' => 'Diseño Editorial', 'codigo' => 'DG-301', 'descripcion' => 'Maquetación de revistas, libros y catálogos con Adobe InDesign.'],
            ['nombre' => 'Identidad Corporativa', 'codigo' => 'DG-302', 'descripcion' => 'Creación de marcas, logotipos y manuales de marca.'],
            ['nombre' => 'Diseño Publicitario', 'codigo' => 'DG-303', 'descripcion' => 'Estrategias de comunicación para anuncios impresos y digitales.'],
            ['nombre' => 'Animación Digital', 'codigo' => 'DG-304', 'descripcion' => 'Principios de animación 2D con Adobe After Effects.'],
            // Semestre 4
            ['nombre' => 'Portafolio Profesional', 'codigo' => 'DG-401', 'descripcion' => 'Preparación de proyectos personales para la inserción laboral.'],
            ['nombre' => 'Producción Audiovisual', 'codigo' => 'DG-402', 'descripcion' => 'Edición de video y postproducción con Adobe Premiere.'],
            ['nombre' => 'Diseño UX/UI', 'codigo' => 'DG-403', 'descripcion' => 'Diseño de interfaces web y móviles centradas en el usuario.'],
            ['nombre' => 'Costos y Presupuestos de Diseño', 'codigo' => 'DG-404', 'descripcion' => 'Gestión financiera para diseñadores independientes (Freelance).'],
            */

            // --- MARKETING DIGITAL (TA-MD) ---
            // Semestre 1
            ['nombre' => 'Fundamentos del Marketing', 'codigo' => 'MD-101', 'descripcion' => 'Las 4 Ps del marketing y estrategias básicas de mercadeo.'],
            ['nombre' => 'Comportamiento del Consumidor', 'codigo' => 'MD-102', 'descripcion' => 'Estudio de motivaciones, necesidades y patrones de compra.'],
            ['nombre' => 'Redacción Creativa', 'codigo' => 'MD-103', 'descripcion' => 'Copywriting orientado a redes sociales y blogs.'],
            ['nombre' => 'Introducción a Redes Sociales', 'codigo' => 'MD-104', 'descripcion' => 'Gestión y análisis de perfiles de marcas en Facebook, Instagram y TikTok.'],
            // Semestre 2
            ['nombre' => 'Estrategia de Marketing Digital', 'codigo' => 'MD-201', 'descripcion' => 'Plan de marketing digital e inbound marketing.'],
            ['nombre' => 'SEO y SEM', 'codigo' => 'MD-202', 'descripcion' => 'Posicionamiento orgánico y de pago en buscadores (Google Ads).'],
            ['nombre' => 'Analítica Web', 'codigo' => 'MD-203', 'descripcion' => 'Medición de métricas con Google Analytics.'],
            ['nombre' => 'Publicidad Online', 'codigo' => 'MD-204', 'descripcion' => 'Creación de campañas publicitarias en Meta Ads.'],
        ];

        foreach ($materias as $materia) {
            Materia::create($materia);
        }
    }
}
