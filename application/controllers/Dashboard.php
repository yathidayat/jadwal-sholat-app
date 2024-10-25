<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url'); // Memuat helper URL di sini
    }

    public function jadwal_sholat(){
            $this->load->view('sholat_view');
    }

    public function get_jadwal_sholat()
    {
        try {
            // Mengambil input kabupaten dari request AJAX
            $kota = $this->input->post('kabupaten'); // Menggunakan post karena kita akan kirim data via POST

            // Var_dump($kota);exit(); 



            // $kota = "HULU SUNGAI TENGAH";


            // Ambil data kota dan ID kota
            $dataKota = $this->get_kota($kota); // Kirim nama atau ID kabupaten ke function get_kota

            // var_dump($dataKota);exit(); 

            if (!isset($dataKota->data->id)) {
                throw new Exception('ID Kota tidak ditemukan.');
            }
            $idKota = $dataKota->data->id;

            // Ambil tanggal sekarang
            $dateTime = date('Y-m-d');

            // URL API yang akan diakses
            $url = "https://api.myquran.com/v2/sholat/jadwal/{$idKota}/{$dateTime}";

            // Inisialisasi cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            // Eksekusi cURL
            $response = curl_exec($ch);

            // Mengecek apakah terjadi kesalahan saat eksekusi cURL
            if (curl_errno($ch)) {
                throw new Exception('Error: ' . curl_error($ch));
            }

            // Mendekode respons JSON menjadi array PHP
            $response_data = json_decode($response, true);

            // Menutup sesi cURL
            curl_close($ch);

            // Mengecek apakah status dari respons adalah true dan data tersedia
            if ($response_data['status'] === true && isset($response_data['data'])) {
                // Mengirimkan hanya elemen 'data' ke view
                echo json_encode($response_data['data']); // Kembalikan response dalam format JSON
            } else {
                echo json_encode(['error' => 'Data tidak ditemukan atau status API tidak valid.']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }


    public function get_kota($kota)
    {
        try {
            $dataKota = $this->get_cari_kota($kota);

            // Pastikan $dataKota bukan null dan memiliki properti 'data'
            if (!$dataKota || !isset($dataKota->data) || empty($dataKota->data)) {
                echo "Kota Tidak Ditemukan";
                return;
            }

            // Mengambil ID kota dari hasil API dan cek dengan isset
            if (isset($dataKota->data[0]->id)) {
                $id_kota = $dataKota->data[0]->id;
            } else {
                echo "ID Tidak Ditemukan";
                return;
            }

            // URL API yang akan diakses untuk mengambil detail kota
            $url = "https://api.myquran.com/v2/sholat/kota/{$id_kota}";

            // Inisialisasi cURL
            $ch = curl_init();

            // Set opsi cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            // Eksekusi cURL
            $response = curl_exec($ch);

            // Mengecek apakah terjadi kesalahan saat eksekusi cURL
            if (curl_errno($ch)) {
                throw new Exception('Error: ' . curl_error($ch));
            }

            // Mendekode respons JSON menjadi objek PHP
            $decoded_response = json_decode($response);

            // Mengecek apakah response dari API valid
            if (!$decoded_response || !isset($decoded_response->data)) {
                echo "Data kota tidak ditemukan dalam respons API.";
                return;
            }

            // Menutup sesi cURL
            curl_close($ch);

            // Mengembalikan data yang telah didekode
            return $decoded_response;
        } catch (\Throwable $th) {
            // Tangkap kesalahan dan tampilkan pesan error
            echo "Terjadi kesalahan: " . $th->getMessage();
        }
    }
    


    public function get_cari_kota($kota)
    {
        try {
            // URL API yang akan diakses
            $url = "https://api.myquran.com/v2/sholat/kota/cari/{$kota}";

            // Inisialisasi cURL
            $ch = curl_init();

            // Set opsi cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            // Menonaktifkan verifikasi sertifikat SSL (hanya digunakan untuk development lokal)
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            // Eksekusi cURL
            $response = curl_exec($ch);

            // Mengecek apakah terjadi kesalahan saat eksekusi cURL
            if (curl_errno($ch)) {
                throw new Exception('Error: ' . curl_error($ch));
            }

            // Mendekode respons JSON menjadi array PHP
            $decoded_response = json_decode($response);

            // var_dump($decoded_response);exit();

            // Menutup sesi cURL
            curl_close($ch);

            // Mengembalikan data yang telah didekode
            return $decoded_response;
        } catch (\Throwable $th) {
            // Menangkap kesalahan dan mengembalikan pesan kesalahan
            return ['error' => $th->getMessage()];
        }
    }

    public function get_province() {
        try {
            $api_url = "https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json";
            $response = file_get_contents($api_url);
            $data = json_decode($response, true);

            if ($data) {
                echo json_encode($data);
            } else {
                echo json_encode(['error' => 'Gagal mengambil data provinsi atau format data tidak sesuai.']);
            }
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function get_kota_kab($id)
    {
        try {
            $api_url = "https://emsifa.github.io/api-wilayah-indonesia/api/regencies/{$id}.json";
            $response = file_get_contents($api_url);
            $data = json_decode($response, true);

            if ($data) {
                echo json_encode($data);
            } else {
                echo json_encode(['error' => 'Gagal mengambil data kabupaten atau format data tidak sesuai.']);
            }
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }    

    
    
    

    
}
