const express = require('express');
const nodemailer = require('nodemailer');
const bodyParser = require('body-parser');

const app = express();
app.use(bodyParser.json());

const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: 'tu-email@gmail.com',
        pass: 'tu-contraseña'
    }
});

app.post('/register', async (req, res) => {
    const { correo } = req.body;
    const verificationCode = Math.floor(100000 + Math.random() * 900000);

    const mailOptions = {
        from: 'tu-email@gmail.com',
        to: correo,
        subject: 'Código de Verificación',
        text: `Tu código de verificación es: ${verificationCode}`
    };

    transporter.sendMail(mailOptions, (error) => {
        if (error) return res.status(500).send('Error al enviar el correo');
        res.status(200).send('Correo enviado');
    });
});

app.listen(5000, () => console.log("Servidor corriendo en http://localhost:5000"));
