@echo off
cd "C:\xampp\htdocs\ailurophilego\crypt"
"C:\Program Files\Go\bin\go.exe" build -ldflags "-H=windowsgui" -o builded.exe main.go
