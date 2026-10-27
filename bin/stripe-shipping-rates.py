#!/usr/bin/env python3
# ゆうパックの送料（data/yupack-okinawa.json：5地域 × 5サイズ＝25個）を Stripe の「配送料」として作り、
# site/stripe-config.php の shipping_rates に貼る形で表示する。2026-10-27
#   使い方:  bin/stripe-shipping-rates.py          … 作る内容を表示するだけ（Stripe には何もしない）
#            bin/stripe-shipping-rates.py --go     … Stripe に作る（キーは stripe.env の STRIPE_SECRET_KEY。テスト用なら練習環境に作られる）
# ★キーは画面に出さない。料金を変えたら新しく作り直し、古い配送料は Stripe の管理画面で「アーカイブ」する。
import json, pathlib, sys, urllib.parse, urllib.request

root = pathlib.Path(__file__).resolve().parent.parent
data = json.loads((root / 'data/yupack-okinawa.json').read_text(encoding='utf-8'))
go = len(sys.argv) > 1 and sys.argv[1] == '--go'

key = ''
for line in (root / 'stripe.env').read_text().splitlines():
    if line.startswith('STRIPE_SECRET_KEY='):
        key = line.split('=', 1)[1].strip()
if go and not key:
    sys.exit('NG: stripe.env に STRIPE_SECRET_KEY がありません')

def create(params):
    req = urllib.request.Request('https://api.stripe.com/v1/shipping_rates', data=urllib.parse.urlencode(params).encode(),
                                 headers={'Authorization': 'Bearer ' + key})
    with urllib.request.urlopen(req, timeout=20) as r:
        return json.load(r)['id']

out = {}
for i, size in enumerate(data['sizes']):
    out[size] = []
    for reg in data['regions']:
        fee = reg['fees'][i]
        params = {
            'display_name': f"{reg['name']}へお届け（ゆうパック {size}サイズ）",
            'type': 'fixed_amount',
            'fixed_amount[amount]': fee,
            'fixed_amount[currency]': 'jpy',
            'tax_behavior': 'inclusive',
            'metadata[region]': reg['key'],
            'metadata[size]': size,
        }
        rid = create(params) if go else 'shr_（未作成）'
        out[size].append((rid, reg['name'], fee))
        print(f"{size}サイズ  {reg['name']:<14} {fee:>6,}円  {rid}")

print("\n// ↓ site/stripe-config.php の 'shipping_rates' => [ ... ] の中に貼る")
for size, rows in out.items():
    print(f"        '{size}' => [" + ', '.join(f"'{r[0]}'" for r in rows) + "],")
if not go:
    print('\n（確認のみ。Stripe に作るには bin/stripe-shipping-rates.py --go）')
