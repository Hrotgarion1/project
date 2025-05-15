@echo off
setlocal

:: Cambiar al directorio del script
cd /d "%~dp0"

:: Crear la carpeta certs si no existe
if not exist "certs" (
    mkdir certs
)

:: Generar certificado para project.test
echo Generando certificado SSL para project.test...
mkcert -key-file certs\project.test-key.pem -cert-file certs\project.test.pem project.test

echo.
echo ✅ Certificados generados correctamente en la carpeta "certs"
echo 🔐 Archivo clave: certs\project.test-key.pem
echo 📄 Certificado:   certs\project.test.pem
pause
