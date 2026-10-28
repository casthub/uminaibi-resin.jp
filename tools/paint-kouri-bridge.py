# 古宇利大橋から古宇利島を望む景色を「絵画調」の SVG で描く（写真はモチーフとして参照するだけで、使わない）
# 使い方: python3 tools/paint-kouri-bridge.py site/images/paint-kouri-bridge.svg（同じ乱数の種なので、何度作っても同じ絵になる）
import random, math, sys
OUT = sys.argv[1]
R = random.Random(20261028)
W, H, HZ = 1200, 900, 560          # 幅・高さ・水平線
VX, VY = 720, 562                  # 橋の消失点

def lerp(a, b, t): return a + (b - a) * t
def hx(c): return tuple(int(c[i:i+2], 16) for i in (1, 3, 5))
def mix(c1, c2, t):
    a, b = hx(c1), hx(c2); return '#%02x%02x%02x' % tuple(round(lerp(x, y, t)) for x, y in zip(a, b))
def grad(stops, t):
    t = max(0, min(1, t))
    for (p0, c0), (p1, c1) in zip(stops, stops[1:]):
        if t <= p1: return mix(c0, c1, (t - p0) / (p1 - p0 or 1))
    return stops[-1][1]
def jit(c, amt=14):
    r, g, b = hx(c); d = lambda v: max(0, min(255, v + R.randint(-amt, amt)))
    return '#%02x%02x%02x' % (d(r), d(g), d(b))

SKY = [(0, '#0e4fb4'), (0.45, '#1f6fd6'), (0.8, '#4f9ae6'), (0.95, '#8cc5f1'), (1, '#b4dcf6')]
SEA = [(0, '#1595c9'), (0.08, '#17b4d8'), (0.35, '#1ccbdc'), (0.7, '#2ed6d6'), (1, '#46dccf')]

o = []
o.append(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {W} {H}" preserveAspectRatio="xMidYMax slice">')
o.append('''<defs>
<linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">''' + ''.join(f'<stop offset="{p}" stop-color="{c}"/>' for p, c in SKY) + '''</linearGradient>
<linearGradient id="sea" x1="0" y1="0" x2="0" y2="1">''' + ''.join(f'<stop offset="{p}" stop-color="{c}"/>' for p, c in SEA) + '''</linearGradient>
<filter id="brush" x="-5%" y="-5%" width="110%" height="110%"><feTurbulence type="fractalNoise" baseFrequency="0.035 0.09" numOctaves="2" seed="3" result="n"/><feDisplacementMap in="SourceGraphic" in2="n" scale="5" xChannelSelector="R" yChannelSelector="G"/></filter>
<filter id="rough" x="-5%" y="-5%" width="110%" height="110%"><feTurbulence type="fractalNoise" baseFrequency="0.06" numOctaves="3" seed="8" result="n"/><feDisplacementMap in="SourceGraphic" in2="n" scale="5" xChannelSelector="R" yChannelSelector="G"/></filter>
<filter id="soft" x="-30%" y="-80%" width="160%" height="260%"><feTurbulence type="fractalNoise" baseFrequency="0.03 0.08" numOctaves="2" seed="5" result="n"/><feDisplacementMap in="SourceGraphic" in2="n" scale="10" xChannelSelector="R" yChannelSelector="G" result="d"/><feGaussianBlur in="d" stdDeviation="7"/></filter>
<filter id="canvas" x="0" y="0" width="100%" height="100%"><feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" seed="11"/><feColorMatrix values="0 0 0 0 0.5  0 0 0 0 0.5  0 0 0 0 0.5  0 0 0 -1.1 0.75"/></filter>
</defs>''')

# 空
o.append(f'<rect width="{W}" height="{HZ+4}" fill="url(#sky)"/>')
g = []
for _ in range(520):
    y = R.random() ** 0.9 * (HZ - 6)
    L = R.uniform(70, 260); x = R.uniform(-150, W)
    c = jit(grad(SKY, y / HZ), 16)
    if R.random() < 0.18: c = mix(c, '#ffffff', R.uniform(0.25, 0.5))
    w = R.uniform(5, 18); dy = R.uniform(-14, 6)
    g.append(f'<path d="M{x:.0f} {y:.0f} Q{x+L/2:.0f} {y+dy:.0f} {x+L:.0f} {y+R.uniform(-6,6):.0f}" stroke="{c}" stroke-width="{w:.0f}" opacity="{R.uniform(0.12,0.3):.2f}"/>')
# 水平線近くの薄い雲
for _ in range(14):
    x = R.uniform(-50, W); y = R.uniform(HZ - 70, HZ - 20)
    g.append(f'<ellipse cx="{x:.0f}" cy="{y:.0f}" rx="{R.uniform(50,140):.0f}" ry="{R.uniform(5,12):.0f}" fill="#ffffff" opacity="{R.uniform(0.15,0.35):.2f}" filter="url(#soft)"/>')
o.append('<g fill="none" stroke-linecap="round" filter="url(#brush)">' + ''.join(g) + '</g>')

# 海（全体の地）
o.append(f'<rect y="{HZ}" width="{W}" height="{H-HZ}" fill="url(#sea)"/>')
# 沖の濃い帯（橋の右側にも続く）
o.append(f'<rect y="{HZ}" width="{W}" height="10" fill="#1386bd" opacity="0.8" filter="url(#rough)"/>')

# 古宇利島
isl = [(90,568),(130,548),(190,532),(260,520),(330,506),(420,498),(500,496),(580,500),(650,506),(700,516),(740,530),(770,548),(790,566)]
d = 'M' + ' L'.join(f'{x} {y}' for x, y in isl) + ' Z'
o.append(f'<g filter="url(#rough)"><path d="{d}" fill="#2f6b3c"/>')
def inside(px, py):
    if py > 566: return False
    for (x0,y0),(x1,y1) in zip(isl, isl[1:]):
        if x0 <= px <= x1:
            yy = lerp(y0, y1, (px-x0)/(x1-x0)); return py >= yy
    return False
dabs = []
for _ in range(900):
    px, py = R.uniform(90, 790), R.uniform(494, 566)
    if not inside(px, py): continue
    c = R.choice(['#3f7d40','#4f8f42','#5d9a48','#2c5f36','#77ad55','#94c063','#3a7146'])
    dabs.append(f'<ellipse cx="{px:.0f}" cy="{py:.0f}" rx="{R.uniform(3,9):.0f}" ry="{R.uniform(2,5):.0f}" fill="{c}" opacity="{R.uniform(0.5,0.9):.2f}"/>')
o.append(''.join(dabs))
# 家並み（白い小さな点）
for _ in range(70):
    px = R.uniform(230, 720); py = R.uniform(530, 562)
    if not inside(px, py - 3): continue
    w = R.uniform(4, 10); h = R.uniform(3, 6)
    o.append(f'<rect x="{px:.0f}" y="{py:.0f}" width="{w:.0f}" height="{h:.0f}" fill="{R.choice(["#ffffff","#f4efe2","#e9eef0","#f8e9d8"])}" opacity="{R.uniform(0.7,0.95):.2f}"/>')
o.append('<rect x="684" y="500" width="7" height="18" fill="#ffffff"/>')   # 白い建物
o.append('</g>')
# 島の前の白い砂浜
o.append('<path d="M250 566 C380 562 560 563 790 566 L790 570 L250 570 Z" fill="#f6f0dc" opacity="0.9" filter="url(#rough)"/>')

# 海の筆あと（遠くは細く、手前は太く）
g = []
for _ in range(420):
    t = R.random() ** 1.6; y = HZ + 8 + t * (H - HZ - 8)
    s = 0.25 + t * 1.6
    L = R.uniform(60, 260) * s; x = R.uniform(-100, W)
    c = jit(grad(SEA, t), 18)
    r = R.random()
    if r < 0.2: c = mix(c, '#ffffff', R.uniform(0.35, 0.65))
    elif r < 0.35: c = mix(c, '#0f7fa0', R.uniform(0.3, 0.6))
    w = R.uniform(3, 10) * s
    g.append(f'<path d="M{x:.0f} {y:.0f} Q{x+L/2:.0f} {y+R.uniform(-3,3)*s:.0f} {x+L:.0f} {y+R.uniform(-2,2)*s:.0f}" stroke="{c}" stroke-width="{w:.1f}" opacity="{R.uniform(0.18,0.42):.2f}"/>')
o.append('<g fill="none" stroke-linecap="round" filter="url(#brush)">' + ''.join(g) + '</g>')
# サンゴ礁の影（濃い青緑のにじみ）
for cx, cy, rx, ry, op in [(430,655,150,26,0.55),(110,612,80,9,0.4),(330,604,50,6,0.35),(240,700,60,10,0.35),(780,865,140,40,0.45),(560,620,40,5,0.3),(60,760,70,14,0.3)]:
    o.append(f'<ellipse cx="{cx}" cy="{cy}" rx="{rx}" ry="{ry}" fill="#0f6c7e" opacity="{op}" filter="url(#soft)"/>')
# きらめき
for _ in range(60):
    t = R.random() ** 1.3; y = HZ + 12 + t * (H - HZ - 20); x = R.uniform(0, 820)
    o.append(f'<ellipse cx="{x:.0f}" cy="{y:.0f}" rx="{1.5+t*5:.1f}" ry="{0.6+t*1.6:.1f}" fill="#ffffff" opacity="{R.uniform(0.4,0.85):.2f}"/>')

# 古宇利大橋（手前右から島へのびる）
def P(t, ex, ey): return (lerp(VX, ex, t), lerp(VY, ey, t))
LX, LY = 930, 900      # 左のふち（手すりの根元）が画面下に届く所
RX, RY = 1560, 760     # 右のふち（画面の外）
b = ['<g>']
# 橋の側面（コンクリート）
b.append(f'<path d="M{VX} {VY} L{VX+3} {VY+1} L{LX+6} {LY} L{LX-80} {LY} Z" fill="#b9b4a4"/>')
b.append(f'<path d="M{VX} {VY} L{LX-80} {LY} L{LX-62} {LY} Z" fill="#8f8a7c" opacity="0.6"/>')
# 路面
b.append(f'<path d="M{VX} {VY} L{RX} {RY} L{RX} {H+10} L{LX} {H+10} Z" fill="#d6d9d8"/>')
for _ in range(60):
    t = R.uniform(0.1, 1.2); u = R.uniform(0.05, 0.95)
    x0, y0 = P(t, lerp(LX, RX, u), lerp(LY, RY, u)); x1, y1 = P(min(1.4, t + R.uniform(0.08, 0.25)), lerp(LX, RX, u), lerp(LY, RY, u))
    b.append(f'<path d="M{x0:.0f} {y0:.0f} L{x1:.0f} {y1:.0f}" stroke="{R.choice(["#c4c8c8","#e6e8e6","#bfc3c4","#eceeed"])}" stroke-width="{2+t*7:.1f}" opacity="0.5" stroke-linecap="round"/>')
# 白い路肩と黄色い中央の線
b.append(f'<path d="M{VX} {VY} L{LX+40} {H+10} L{LX+120} {H+10} Z" fill="#f2f2ee" opacity="0.9"/>')
cx_, cy_ = lerp(LX, RX, 0.55), lerp(LY, RY, 0.55)
b.append(f'<path d="M{VX} {VY} L{cx_:.0f} {cy_:.0f}" stroke="#e8c45a" stroke-width="5" opacity="0.7"/>')
# 奥の手すり（右側）
b.append(f'<path d="M{VX} {VY-1} L1200 {VY+120} L1200 {VY+150} L{VX} {VY+1} Z" fill="#9fd3ef" opacity="0.7"/>')
# 手前の手すり（白い柱と水色の横棒。柱のすき間から海が見える）
def rail(t): x, y = P(t, LX, LY); return x, y, y - 250 * t
ts = [i/60 for i in range(0, 121)]
top = [rail(t) for t in ts]
def band(off0, off1):  # 手すりの横棒（遠くは細く、手前は太く）
    up = ' '.join(f'{x:.1f},{yt+off0*t:.1f}' for (x, y, yt), t in zip(top, ts))
    dn = ' '.join(f'{x:.1f},{yt+off1*t:.1f}' for (x, y, yt), t in reversed(list(zip(top, ts))))
    return up + ' ' + dn
t = 0.02
while t < 2.0:
    x, y, yt = rail(t)
    b.append(f'<path d="M{x:.1f} {y:.1f} L{x:.1f} {yt:.1f}" stroke="#ffffff" stroke-width="{0.6+t*4.5:.1f}" opacity="0.95"/>')
    b.append(f'<path d="M{x+0.6+t*2.6:.1f} {y:.1f} L{x+0.6+t*2.6:.1f} {yt:.1f}" stroke="#b7d3e0" stroke-width="{0.3+t*1.5:.1f}" opacity="0.7"/>')
    t *= 1.07 if t > 0.12 else 1.3
b.append(f'<polygon points="{band(-2, 16)}" fill="#3aa6e0"/>')
b.append(f'<polygon points="{band(16, 24)}" fill="#86cbef"/>')
b.append(f'<polygon points="{band(232, 250)}" fill="#3aa6e0"/>')
b.append('</g>')
o += b

# キャンバスの布目（全体にうっすら）
o.append(f'<rect width="{W}" height="{H}" filter="url(#canvas)" opacity="0.35" style="mix-blend-mode:multiply"/>')
o.append('</svg>')
open(OUT, 'w').write('\n'.join(o))
