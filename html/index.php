<?php

/**
 * Class to create amd store the NIS number
 */
class nis_number
{
    public $db;

    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Only connect to a mysql database
     */
    private function db_connect()
    {
        $this->db = new mysqli("mysql", "root", "root", "inss_db");
    }

    /**
     * Generate randomly NIS number
     *
     * @return string NIS number with mask
     */
    private function generate_nis()
    {
        $nis =
            rand(100, 999) .
            "." .
            rand(10000, 99999) .
            "." .
            rand(10, 99) .
            "-" .
            rand(0, 9);

        while ($this->repeated_nis($nis) != 0) {
            $nis =
                rand(100, 999) .
                "." .
                rand(10000, 99999) .
                "." .
                rand(10, 99) .
                "-" .
                rand(0, 9);
        }

        return $nis;
    }

    /**
     * Validate if generated NIS number is repeated
     *
     * @param string $nis NIS number.
     * @return int Number of rows to know if NIS is repeated
     */
    private function repeated_nis($nis)
    {
        $query =
            'SELECT register.id, register.name, register.nis FROM register WHERE register.nis = "' .
            $nis .
            '";';
        ($data = $this->db->execute_query($query)) or
            die(mysqli_error($this->db));

        return $data->num_rows;
    }

    /**
     * Insert a new NIS register in database
     */
    public function insert_data()
    {
        $name = $_POST["name"];
        $nis = $this->generate_nis();

        $query =
            'INSERT INTO register (register.name, register.nis)
        VALUES ("' .
            $name .
            '", "' .
            $nis .
            '")';

        $this->db->execute_query($query) or die(mysqli_error($this->db));

        header("location: /");
    }

    /**
     * Get a storaged register of NIS
     *
     * @param string $nis NIS number.
     * @return array Array with NIS and Name
     */
    public function get_by_nis($nis)
    {
        $query =
            'SELECT register.id, register.name, register.nis FROM register WHERE register.nis = "' .
            $nis .
            '";';
        ($data = $this->db->execute_query($query)) or
            die(mysqli_error($this->db));

        return $this->format_data($data);
    }

    /**
     * Get all storaged registers of NIS to contruct the NIS list table
     *
     * @return array Array with NIS and Name
     */
    public function get_all()
    {
        $query =
            "SELECT register.id, register.name, register.nis FROM register;";

        ($data = $this->db->execute_query($query)) or
            die(mysqli_error($this->db));

        return $this->format_data($data);
    }

    /**
     * Format the list of NIS
     *
     * @param object $result NIS list.
     * @return array Array with NIS and Name
     */
    private function format_data($result)
    {
        $data = [];
        $key = 0;
        while ($row = $result->fetch_array(MYSQLI_BOTH)) {
            $data[$key] = [
                "name" => $row["name"],
                "nis" => $row["nis"],
            ];

            $key++;
        }

        return $data;
    }
}

//Instace of nis_number class
$nis_number = new nis_number();

//Selection of all registers to construct list table
$all_registers = $nis_number->get_all();

//Post to save new registers
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nis_number->insert_data();
}

//Return data and status 1 if exists or message and status 0 if not exists,
if (strtolower($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "") === "xmlhttprequest") {
    $nis_data = $nis_number->get_by_nis($_GET["nis"]);
    if ($nis_data) {
        echo json_encode(array_merge(["status" => 1], $nis_data));
    } else {
        echo json_encode([
            "status" => 0,
            "message" => "NIS não encontrado em nossa base de dados.",
        ]);
    }
    exit();
}

?>

<html>
    <head>
        <title>NIS</title>
        <link rel="stylesheet" href="http://localhost/assets/css/style.css">
    </head>
    <body>
        <h1>Cadastro NIS</h1>
        <div>
            <h2>Formulário</h2>
            <form id="form" method="post" action="http://localhost/">
                <div>
                    <input name="name" type="text" placeholder="Insira um nome válido">
                </div>
                <button id="send">
                    Enviar
                </button>
            </form>
        </div>
        <div>
            <h2>Registros</h2>
            <table>
                <div class="top-search">
                    <input id="search_nis" type="text" placeholder="Insira o número do NIS">
                    <button id="search">
                        Buscar
                    </button>
                </div>
                <thead>
                    <th>Nome</th>
                    <th>Nis</th>
                </thead>
                <tbody>
                    <?php foreach ( $all_registers as $registers ): ?>
                    <tr>
                        <td><?php echo $registers['name']; ?></td>
                        <td><?php echo $registers['nis']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </body>
    <script
			  src="https://code.jquery.com/jquery-3.7.1.min.js"
			  integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
			  crossorigin="anonymous"></script>
    <script
			  src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"
			  crossorigin="anonymous"></script>              
    <script type="text/javascript">
        $('#search_nis').mask('000.00000.00-0');
        $('#send').on('click', function( event ) {
            event.preventDefault();
            $('#form input').each(function() {
                if(!$(this).val()) {
                    alert('Favor preencher o nome');
                    return false;
                }  
                
                $('#form').submit();
                return true;
            });
        });
        $('#search').on('click', function( event ) {
            if(!$('#search_nis').val()) {
                alert('Favor preencher o nis');
                return false;
            }  
            $.ajax({
                method: "GET",
                url: "http://localhost/",
                dataType: 'json',
                data: { nis: $('#search_nis').val() }
            }).done(function( data ) {
                console.log(data);
                if ( data.status ) {
                    alert( "O NIS " + data[0].nis + " pertence a " + data[0].name );
                } else {
                    alert( data.message );
                }
            });
        });
    </script>
</html>