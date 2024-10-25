<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>JADWAL SHOLAT INDONESIA</title>
</head>

<body class="bg-gray-100">
    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-2xl">
            <h1 class="text-3xl font-bold text-center text-teal-600 mb-6">JADWAL SHOLAT INDONESIA</h1>

            <!-- Select Provinsi dan Kabupaten -->
            <div class="flex space-x-4 mb-6">
                <select id="provinsi" class="w-1/2 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 transition duration-200 ease-in-out transform hover:scale-105">
                    <option value="" disabled selected>-- Pilih Provinsi --</option>
                    <!-- Option provinsi akan diisi dengan data dinamis -->
                </select>

                <select id="kabupaten" class="w-1/2 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 transition duration-200 ease-in-out transform hover:scale-105">
                    <option value="" disabled selected>-- Pilih Kabupaten --</option>
                    <!-- Option kabupaten akan diisi dengan data dinamis -->
                </select>
            </div>

            <!-- Bagian Jadwal Sholat -->
            <h4 class="text-xl font-semibold text-center text-teal-600 mb-4">Jadwal Sholat Wilayah <span id="lokasi-kota"></span></h4>
            
            <div class="overflow-x-auto">
                <table class="table-auto w-full text-left border-collapse">
                    <tbody id="jadwal-body">
                        <!-- Jika data belum dipilih -->
                        <tr>
                            <td colspan="2" class="border px-4 py-2 text-red-500 text-center font-bold">Silakan pilih wilayah terlebih dahulu</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Jika data tidak ditemukan -->
            <p id="data-not-found" class="text-center text-red-500 hidden">Data tidak ditemukan atau belum dipilih.</p>
        </div>
    </div>

    <!-- Pastikan Anda menyertakan jQuery sebelum menggunakan script ini -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Mengambil data provinsi saat halaman dimuat
            $.ajax({
                url: '<?= site_url('dashboard/get_province') ?>',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    var selectProvinsi = $('#provinsi');
                    $.each(response, function(index, province) {
                        selectProvinsi.append(
                            $('<option></option>').val(province.id).text(province.name)
                        );
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error: ' + error);
                }
            });

            // Mengambil data kabupaten ketika provinsi dipilih
            $('#provinsi').change(function() {
                var provinsiId = $(this).val();
                var selectKabupaten = $('#kabupaten');
                selectKabupaten.empty();
                selectKabupaten.append('<option value="" disabled selected>-- Pilih Kabupaten --</option>');

                // Mengambil data kabupaten berdasarkan provinsi yang dipilih
                $.ajax({
                    url: '<?= site_url('dashboard/get_kota_kab') ?>/' + provinsiId,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $.each(response, function(index, kabupaten) {
                            selectKabupaten.append(
                                $('<option></option>').val(kabupaten.name).text(kabupaten.name)
                            );
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error: ' + error);
                    }
                });
            });

            // Mengambil jadwal sholat ketika kabupaten dipilih
            $('#kabupaten').change(function() {
                var kabupatenId = $(this).val();
                kabupatenId = kabupatenId.replace(/^(KABUPATEN|KOTA)\s*/i, '');

                $.ajax({
                    url: '<?= site_url('dashboard/get_jadwal_sholat') ?>',
                    method: 'POST',
                    data: {
                        kabupaten: kabupatenId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.jadwal) {
                            $('#lokasi-kota').text(response.lokasi);
                            var jadwal = response.jadwal;

                            // Log untuk melihat struktur jadwal
                            console.log(jadwal);

                            var jadwalRows = `
                                <tr class="bg-gray-50">
                                    <td class="border px-4 py-2 text-teal-600 font-bold">Subuh</td>
                                    <td class="border px-4 py-2 text-gray-700">${jadwal.subuh || '00:00'}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border px-4 py-2 text-teal-600 font-bold">Zuhur</td>
                                    <td class="border px-4 py-2 text-gray-700">${jadwal.dzuhur || '00:00'}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border px-4 py-2 text-teal-600 font-bold">Asar</td>
                                    <td class="border px-4 py-2 text-gray-700">${jadwal.ashar || '00:00'}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border px-4 py-2 text-teal-600 font-bold">Maghrib</td>
                                    <td class="border px-4 py-2 text-gray-700">${jadwal.maghrib || '00:00'}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border px-4 py-2 text-teal-600 font-bold">Isya</td>
                                    <td class="border px-4 py-2 text-gray-700">${jadwal.isya || '00:00'}</td>
                                </tr>`;
                            $('#jadwal-body').html(jadwalRows);

                            // Sembunyikan notifikasi "data tidak ditemukan"
                            $('#data-not-found').addClass('hidden');
                        } else {
                            $('#data-not-found').removeClass('hidden');
                            
                            // Tampilkan pesan default
                            $('#jadwal-body').html(`
                                <tr>
                                    <td colspan="2" class="border px-4 py-2 text-red-500 text-center font-bold">Silakan pilih wilayah terlebih dahulu</td>
                                </tr>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error: ' + error);
                    }
                });
            });
        });
    </script>
</body>

</html>
