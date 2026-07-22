from __future__ import annotations

import json
import sys
import urllib.request
from pathlib import Path
from typing import Any


SOURCE_URL = "https://www.sciurus.tel/media/com_iptv/base.json"

ROOT_DIR = Path(__file__).resolve().parent.parent
OUTPUT_FILE = ROOT_DIR / "base.txt"

LOGO_BASE_URL = "https://www.sciurus.tel"


def descargar_json(url: str) -> list[dict[str, Any]]:
    request = urllib.request.Request(
        url,
        headers={
            "User-Agent": "SATIPTV-GitHub-Action/1.0",
            "Accept": "application/json",
        },
    )

    with urllib.request.urlopen(request, timeout=30) as response:
        if response.status != 200:
            raise RuntimeError(
                f"El servidor respondió con HTTP {response.status}"
            )

        contenido = response.read().decode("utf-8-sig")

    datos = json.loads(contenido)

    if not isinstance(datos, list):
        raise ValueError("El JSON remoto no contiene una lista de canales.")

    return datos


def limpiar_texto(valor: Any) -> str:
    if valor is None:
        return ""

    return str(valor).strip()


def frecuencia_preferida(canal: dict[str, Any]) -> str:
    frecuencia_hd = limpiar_texto(canal.get("frec_hd"))
    frecuencia_sd = limpiar_texto(canal.get("frec_sd"))

    return frecuencia_hd or frecuencia_sd


def construir_logo(canal: dict[str, Any]) -> str:
    ruta = limpiar_texto(canal.get("img"))

    if not ruta:
        return ""

    if ruta.startswith("http://") or ruta.startswith("https://"):
        return ruta

    if not ruta.startswith("/"):
        ruta = "/" + ruta

    return LOGO_BASE_URL + ruta.replace(" ", "%20")


def escapar_atributo(valor: str) -> str:
    return valor.replace('"', "'")


def crear_extinf(canal: dict[str, Any]) -> str:
    nombre = limpiar_texto(canal.get("name")) or "Canal sin nombre"
    epg = escapar_atributo(limpiar_texto(canal.get("epg")))
    pais = limpiar_texto(canal.get("country")).upper() or "ES"
    logo = escapar_atributo(construir_logo(canal))

    atributos: list[str] = []

    if epg:
        atributos.append(f'tvg-id="{epg}"')

    if logo:
        atributos.append(f'tvg-logo="{logo}"')

    atributos.append(f'group-title="{pais}"')

    texto_atributos = " ".join(atributos)

    return f"#EXTINF:-1 {texto_atributos},{nombre}"


def generar_m3u(canales: list[dict[str, Any]]) -> tuple[str, int, int]:
    lineas = ['#EXTM3U x-tvg-url="local_xmltv.xml"']

    incluidos = 0
    omitidos = 0
    frecuencias_usadas: set[str] = set()

    for canal in canales:
        if not isinstance(canal, dict):
            omitidos += 1
            continue

        frecuencia = frecuencia_preferida(canal)

        if not frecuencia:
            omitidos += 1
            continue

        frecuencia = frecuencia.lstrip("/")

        # Evita duplicados exactos por frecuencia.
        if frecuencia in frecuencias_usadas:
            omitidos += 1
            continue

        frecuencias_usadas.add(frecuencia)

        lineas.append(crear_extinf(canal))
        lineas.append(f"http://IP:8001/{frecuencia}")

        incluidos += 1

    lineas.append("")
    lineas.append(
        f"# Canales generados automáticamente: {incluidos} | "
        f"Omitidos: {omitidos}"
    )

    return "\n".join(lineas) + "\n", incluidos, omitidos


def main() -> int:
    try:
        canales = descargar_json(SOURCE_URL)
        resultado, incluidos, omitidos = generar_m3u(canales)

        OUTPUT_FILE.write_text(resultado, encoding="utf-8", newline="\n")

        print(f"base.txt actualizado correctamente.")
        print(f"Canales incluidos: {incluidos}")
        print(f"Canales omitidos: {omitidos}")
        print(f"Archivo: {OUTPUT_FILE}")

        return 0

    except Exception as error:
        print(f"ERROR actualizando base.txt: {error}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
