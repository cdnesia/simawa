<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>WhatsApp Bot Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            max-width: 800px;
        }

        h2 {
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        form {
            margin-bottom: 30px;
            padding: 10px;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input[type=text],
        textarea,
        select {
            width: 100%;
            padding: 6px;
            box-sizing: border-box;
            margin-top: 5px;
        }

        button {
            margin-top: 10px;
            padding: 10px 15px;
            cursor: pointer;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        small {
            color: #555;
        }
    </style>
</head>

<body>
    <h2>Scan QR Code WhatsApp</h2>

    @if ($qrDataUri)
        <img src="{{ $qrDataUri }}" alt="QR Code">
    @else
        <p>QR Code tidak tersedia. Mungkin sudah login.</p>
    @endif


    <h1>WhatsApp Bot Interface</h1>

    @if (session('status'))
        <p class="success">{{ session('status') }}</p>
    @endif
    @if (session('error'))
        <p class="error">{{ session('error') }}</p>
    @endif

    {{-- Kirim Pesan Teks --}}
    <h2>Kirim Pesan Teks</h2>
    <form method="POST" action="{{ url('/whatsapp/send-text') }}">
        @csrf
        <label>Nomor WhatsApp</label>
        <input type="text" name="number" placeholder="6281234567890" required>

        <label>Pesan</label>
        <textarea name="message" rows="3" required></textarea>

        <button type="submit">Kirim Pesan Teks</button>
    </form>

    {{-- Kirim Media --}}
    <h2>Kirim Media (Gambar, Video, dll)</h2>
    <form method="POST" action="{{ url('/whatsapp/send-media') }}" enctype="multipart/form-data">
        @csrf
        <label>Nomor WhatsApp</label>
        <input type="text" name="number" placeholder="6281234567890" required>

        <label>File Media</label>
        <input type="file" name="media" required>

        <label>Caption (optional)</label>
        <input type="text" name="caption" placeholder="Keterangan media">

        <button type="submit">Kirim Media</button>
    </form>

    {{-- Kirim Lokasi --}}
    <h2>Kirim Lokasi</h2>
    <form method="POST" action="{{ url('/whatsapp/send-location') }}">
        @csrf
        <label>Nomor WhatsApp</label>
        <input type="text" name="number" placeholder="6281234567890" required>

        <label>Latitude</label>
        <input type="text" name="latitude" placeholder="-6.200000" required>

        <label>Longitude</label>
        <input type="text" name="longitude" placeholder="106.816666" required>

        <label>Deskripsi (optional)</label>
        <input type="text" name="description" placeholder="Contoh: Lokasi Kantor">

        <button type="submit">Kirim Lokasi</button>
    </form>

    {{-- Kirim Kontak --}}
    <h2>Kirim Kontak</h2>
    <form method="POST" action="{{ url('/whatsapp/send-contact') }}">
        @csrf
        <label>Nomor WhatsApp Penerima</label>
        <input type="text" name="number" placeholder="6281234567890" required>

        <label>Nomor Kontak yang Dikirim</label>
        <input type="text" name="contactNumber" placeholder="6289876543210" required>

        <label>Nama Kontak</label>
        <input type="text" name="contactName" placeholder="Nama Kontak" required>

        <button type="submit">Kirim Kontak</button>
    </form>

    {{-- Kirim Mention --}}
    <h2>Kirim Pesan dengan Mention</h2>
    <form method="POST" action="{{ url('/whatsapp/send-mention') }}">
        @csrf
        <label>Nomor WhatsApp Penerima</label>
        <input type="text" name="number" placeholder="6281234567890" required>

        <label>Pesan</label>
        <textarea name="message" rows="3" required></textarea>

        <label>Nomor yang di-mention (pisah dengan koma)</label>
        <input type="text" name="mentionedNumbers" placeholder="6289876543210, 6281122334455">

        <button type="submit">Kirim Mention</button>
    </form>

    {{-- Kirim Tombol --}}
    <h2>Kirim Tombol Interaktif</h2>
    <form method="POST" action="{{ url('/whatsapp/send-buttons') }}">
        @csrf
        <label>Nomor WhatsApp Penerima</label>
        <input type="text" name="number" placeholder="6281234567890" required>

        <label>Teks Pesan</label>
        <textarea name="text" rows="3" required></textarea>

        <label>Buttons (JSON Array)</label>
        <small>Contoh: [{"id":"btn1","body":"Button 1"},{"id":"btn2","body":"Button 2"}]</small>
        <textarea name="buttons" rows="4" required></textarea>

        <button type="submit">Kirim Tombol</button>
    </form>

    {{-- Kirim List --}}
    <h2>Kirim List Interaktif</h2>
    <form method="POST" action="{{ url('/whatsapp/send-list') }}">
        @csrf
        <label>Nomor WhatsApp Penerima</label>
        <input type="text" name="number" placeholder="6281234567890" required>

        <label>Teks Pesan</label>
        <textarea name="text" rows="3" required></textarea>

        <label>Button Text</label>
        <input type="text" name="buttonText" placeholder="Klik untuk lihat list" required>

        <label>Sections (JSON Array)</label>
        <small>Contoh: [{"title":"Section 1","rows":[{"id":"row1","title":"Row 1","description":"Desc
            1"},{"id":"row2","title":"Row 2"}]}]</small>
        <textarea name="sections" rows="6" required></textarea>

        <button type="submit">Kirim List</button>
    </form>

    {{-- Kirim Pesan ke Grup --}}
    <h2>Kirim Pesan ke Grup</h2>
    <form method="POST" action="{{ url('/whatsapp/send-group-message') }}">
        @csrf
        <label>Nama Grup WhatsApp</label>
        <input type="text" name="groupName" placeholder="Nama Grup" required>

        <label>Pesan</label>
        <textarea name="message" rows="3" required></textarea>

        <button type="submit">Kirim Pesan Grup</button>
    </form>

    {{-- Blast Messages --}}
    <h2>Blast Pesan ke Banyak Kontak</h2>
    <form method="POST" action="{{ url('/whatsapp/blast-messages') }}">
        @csrf
        <label>Kontak (JSON Array)</label>
        <small>Contoh: [{"number":"6281234567890","name":"Budi"},{"number":"6289876543210","name":"Ani"}]</small>
        <textarea name="contacts" rows="5" required></textarea>

        <label>Template Pesan</label>
        <small>Gunakan sebagai placeholder nama</small>
        <textarea name="messageTemplate" rows="3" required></textarea>

        <label>Delay (ms, optional)</label>
        <input type="text" name="delay" placeholder="1000">

        <button type="submit">Blast Pesan</button>
    </form>

    <script>
        // Untuk parsing JSON textarea sebelum submit form tombol, list, blast
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', e => {
                const buttonsField = form.querySelector('textarea[name=buttons]');
                const sectionsField = form.querySelector('textarea[name=sections]');
                const contactsField = form.querySelector('textarea[name=contacts]');

                try {
                    if (buttonsField) {
                        JSON.parse(buttonsField.value);
                    }
                    if (sectionsField) {
                        JSON.parse(sectionsField.value);
                    }
                    if (contactsField) {
                        JSON.parse(contactsField.value);
                    }
                } catch (err) {
                    e.preventDefault();
                    alert('Format JSON tidak valid. Mohon cek kembali input JSON.');
                }
            });
        });
    </script>

</body>

</html>
