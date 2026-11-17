"""Export Auctionator v8 prices as JSON without executing SavedVariables."""
import argparse
import datetime
import io
import json
import re
from pathlib import Path

ESCAPES = {b'n': b'\n', b'r': b'\r', b'"': b'"', b'\\': b'\\', b't': b'\t'}


def unescape(match):
    value = match[1]
    return bytes([int(value)]) if value.isdigit() else ESCAPES[value]


def decode_cbor(data):
    stream = io.BytesIO(data)

    def take(size):
        result = stream.read(size)
        if len(result) != size:
            raise ValueError('Truncated Auctionator record')
        return result

    def item():
        kind, size = divmod(take(1)[0], 32)
        if 24 <= size <= 27:
            size = int.from_bytes(take(1 << (size - 24)), 'big')
        elif size >= 28:
            raise ValueError('Unsupported Auctionator CBOR length')
        if kind == 0:
            return size
        if kind == 1:
            return -1 - size
        if kind in (2, 3):
            return take(size).decode('utf-8')
        if kind == 4:
            return [item() for _ in range(size)]
        if kind == 5:
            return {item(): item() for _ in range(size)}
        if kind == 7 and size in (20, 21, 22):
            return {20: False, 21: True, 22: None}[size]
        raise ValueError(f'Unsupported Auctionator CBOR type {kind}')

    value = item()
    if stream.read(1):
        raise ValueError('Unexpected bytes after Auctionator market')
    return value


def export(path):
    saved = path.read_bytes()
    database = re.search(rb'^AUCTIONATOR_PRICE_DATABASE = \{\r?\n(.*?)^\}', saved, re.M | re.S)
    if database is None or not re.search(rb'\["__dbversion"\]\s*=\s*8,', database[1]):
        raise ValueError('Expected an Auctionator v8 price database')
    prices = []
    for line in database[1].split(b'\n'):
        if not line.strip() or b'["__dbversion"]' in line:
            continue
        market = re.fullmatch(rb'\s*\["([^"]+)"\]\s*=\s*"(.*)",\r?', line)
        if market is None:
            raise ValueError('Unsupported saved market format')
        records = decode_cbor(re.sub(rb'\\([0-9]{3}|.)', unescape, market[2]))
        for key, record in records.items():
            if isinstance(record, dict):
                prices.append({'market': market[1].decode(), 'item_key': str(key), 'data': record})
    return {
        'source': 'auctionator',
        'captured_at': datetime.datetime.fromtimestamp(path.stat().st_mtime, datetime.timezone.utc).isoformat(),
        'prices': prices,
    }


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('saved_variables', type=Path)
    args = parser.parse_args()
    print(json.dumps(export(args.saved_variables), ensure_ascii=False))
