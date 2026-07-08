<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industrial Control Tower</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #081018;
            --panel: #0f1e2b;
            --panel-strong: #13283b;
            --line: #21405b;
            --text: #dbebf6;
            --muted: #9db5c9;
            --accent: #1eb4a5;
            --accent-alt: #ff8e3c;
            --critical: #f35b69;
            --ok: #6bd18f;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 92% 8%, rgba(30, 180, 165, 0.18), transparent 32%),
                radial-gradient(circle at 18% 2%, rgba(255, 142, 60, 0.2), transparent 28%),
                linear-gradient(180deg, #060d15 0%, #09131d 100%);
            min-height: 100vh;
        }

        .shell {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 1.2rem;
            max-width: 1440px;
            margin: 0 auto;
            padding: 1.2rem;
        }

        .nav {
            position: sticky;
            top: 1rem;
            height: fit-content;
            background: rgba(15, 30, 43, 0.92);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1rem;
            backdrop-filter: blur(8px);
        }

        .brand {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            margin-bottom: 1rem;
        }

        .brand strong {
            color: #fff;
            font-size: 1.05rem;
            letter-spacing: 0.02em;
        }

        .brand span {
            color: var(--muted);
            font-size: 0.78rem;
            font-family: 'IBM Plex Mono', monospace;
        }

        .nav a {
            display: block;
            padding: 0.62rem 0.72rem;
            margin-bottom: 0.32rem;
            text-decoration: none;
            color: var(--text);
            border-radius: 10px;
            border: 1px solid transparent;
            transition: all .2s ease;
        }

        .nav a:hover {
            border-color: var(--line);
            background: rgba(30, 180, 165, 0.11);
        }

        .content {
            display: grid;
            gap: 1rem;
        }

        .panel {
            background: rgba(15, 30, 43, 0.92);
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1rem 0.5rem;
        }

        .panel-header h2 {
            margin: 0;
            font-size: 1.1rem;
        }

        .panel-header small {
            color: var(--muted);
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.73rem;
        }

        .panel-body {
            padding: 0.9rem 1rem 1rem;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.7rem;
        }

        .kpi {
            background: var(--panel-strong);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 0.72rem;
        }

        .kpi .label {
            font-size: 0.74rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.25rem;
        }

        .kpi .value {
            font-size: 1.28rem;
            color: #fff;
            font-weight: 600;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.7rem;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        th, td {
            padding: 0.6rem 0.7rem;
            text-align: left;
            border-bottom: 1px solid rgba(33, 64, 91, 0.62);
            font-size: 0.86rem;
        }

        th {
            color: var(--muted);
            font-weight: 500;
            background: #0b1824;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 0.72rem;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 999px;
            font-size: 0.72rem;
            padding: 0.22rem 0.54rem;
            font-family: 'IBM Plex Mono', monospace;
        }

        .tag-critical {
            border-color: rgba(243, 91, 105, 0.5);
            color: #ffb7bf;
            background: rgba(243, 91, 105, 0.14);
        }

        .tag-ok {
            border-color: rgba(107, 209, 143, 0.5);
            color: #c2ffd6;
            background: rgba(107, 209, 143, 0.14);
        }

        .gantt {
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
        }

        .gantt-head {
            padding: 0.55rem 0.7rem;
            background: #0b1824;
            color: var(--muted);
            font-size: 0.72rem;
            font-family: 'IBM Plex Mono', monospace;
            border-bottom: 1px solid var(--line);
        }

        .gantt-row {
            display: grid;
            grid-template-columns: 220px 1fr;
            min-height: 38px;
            border-bottom: 1px solid rgba(33, 64, 91, 0.45);
        }

        .gantt-label {
            padding: 0.58rem 0.7rem;
            display: flex;
            align-items: center;
            gap: 0.52rem;
            border-right: 1px solid rgba(33, 64, 91, 0.45);
            font-size: 0.82rem;
        }

        .gantt-track {
            position: relative;
            padding: 0.38rem 0.42rem;
            background-image: linear-gradient(to right, rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 7.5% 100%;
        }

        .gantt-bar {
            position: absolute;
            top: 7px;
            height: 22px;
            border-radius: 9px;
            background: linear-gradient(90deg, var(--accent), var(--accent-alt));
            border: 1px solid rgba(255,255,255,0.15);
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #04131a;
            font-weight: 700;
            min-width: 44px;
        }

        .empty {
            padding: 1rem;
            border: 1px dashed var(--line);
            border-radius: 12px;
            color: var(--muted);
            font-size: 0.85rem;
        }

        @media (max-width: 1180px) {
            .shell { grid-template-columns: 1fr; }
            .nav { position: static; }
            .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .grid-2 { grid-template-columns: 1fr; }
        }

        @media (max-width: 780px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .gantt-row { grid-template-columns: 1fr; }
            .gantt-label { border-right: none; border-bottom: 1px solid rgba(33, 64, 91, 0.45); }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="nav">
            <div class="brand">
                <strong>Industrial Control Tower</strong>
                <span>{{ $today->toDateString() }} → {{ $windowEnd->toDateString() }}</span>
            </div>
            <a href="#mrp">MRP Cockpit</a>
            <a href="#production">Production View</a>
            <a href="#inventory">Inventory View</a>
            <a href="#bom">BOM Explorer</a>
            <a href="#genealogy">Genealogy Explorer</a>
            <a href="#gantt">Scheduling Gantt</a>
        </aside>

        <main class="content">
            <section id="mrp" class="panel">
                <div class="panel-header">
                    <h2>MRP Cockpit</h2>
                    <small>Demand pressure, material risk, replenishment readiness</small>
                </div>
                <div class="panel-body">
                    <div class="kpi-grid">
                        <div class="kpi"><div class="label">Purchase Signals</div><div class="value">{{ $mrpCockpit['purchase_signals'] }}</div></div>
                        <div class="kpi"><div class="label">Production Signals</div><div class="value">{{ $mrpCockpit['production_signals'] }}</div></div>
                        <div class="kpi"><div class="label">At-Risk Materials</div><div class="value">{{ $mrpCockpit['material_shortages']->count() }}</div></div>
                        <div class="kpi"><div class="label">Avg Material Lead Time</div><div class="value">{{ number_format($mrpCockpit['avg_material_lead_time'], 1) }} d</div></div>
                    </div>
                    <div style="margin-top: .8rem;" class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Type</th>
                                    <th>Free Qty</th>
                                    <th>Safety Stock</th>
                                    <th>Lead Time</th>
                                    <th>Signal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mrpCockpit['material_shortages'] as $shortage)
                                    @php
                                        $free = (float) $shortage->qty_available - (float) $shortage->qty_reserved;
                                    @endphp
                                    <tr>
                                        <td>{{ $shortage->product?->sku }} — {{ $shortage->product?->description }}</td>
                                        <td>{{ $shortage->product?->product_type }}</td>
                                        <td>{{ number_format($free, 2) }}</td>
                                        <td>{{ number_format((float) ($shortage->product?->safety_stock ?? 0), 2) }}</td>
                                        <td>{{ (int) ($shortage->product?->lead_time_days ?? 0) }} d</td>
                                        <td><span class="tag tag-critical">Replenish</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><span class="tag tag-ok">No material shortages detected</span></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="production" class="panel">
                <div class="panel-header">
                    <h2>Production View</h2>
                    <small>Open orders, execution status, completion pressure</small>
                </div>
                <div class="panel-body">
                    <div class="grid-2">
                        <div class="kpi-grid" style="grid-template-columns: repeat(2,minmax(0,1fr));">
                            @forelse($production['status_breakdown'] as $status => $total)
                                <div class="kpi">
                                    <div class="label">{{ str_replace('_', ' ', $status) }}</div>
                                    <div class="value">{{ $total }}</div>
                                </div>
                            @empty
                                <div class="empty">No production status data available.</div>
                            @endforelse
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Product</th>
                                        <th>Status</th>
                                        <th>Planned</th>
                                        <th>Produced</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($production['open_orders'] as $order)
                                        <tr>
                                            <td>{{ $order->order_number }}</td>
                                            <td>{{ $order->product?->sku }}</td>
                                            <td>{{ $order->status }}</td>
                                            <td>{{ number_format((float) $order->quantity_planned, 2) }}</td>
                                            <td>{{ number_format((float) $order->quantity_produced, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5">No open production orders.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <section id="inventory" class="panel">
                <div class="panel-header">
                    <h2>Inventory View</h2>
                    <small>Availability, reserves, in-transit and quality gate stock</small>
                </div>
                <div class="panel-body">
                    <div class="kpi-grid">
                        <div class="kpi"><div class="label">Available</div><div class="value">{{ number_format($inventory['kpis']['available'], 2) }}</div></div>
                        <div class="kpi"><div class="label">Reserved</div><div class="value">{{ number_format($inventory['kpis']['reserved'], 2) }}</div></div>
                        <div class="kpi"><div class="label">In Transit</div><div class="value">{{ number_format($inventory['kpis']['in_transit'], 2) }}</div></div>
                        <div class="kpi"><div class="label">Inspection</div><div class="value">{{ number_format($inventory['kpis']['inspection'], 2) }}</div></div>
                    </div>
                    <div style="margin-top: .8rem;" class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Description</th>
                                    <th>Available</th>
                                    <th>Reserved</th>
                                    <th>Free</th>
                                    <th>Health</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventory['rows'] as $row)
                                    @php
                                        $free = (float) $row->qty_available - (float) $row->qty_reserved;
                                        $safety = (float) ($row->product?->safety_stock ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $row->product?->sku }}</td>
                                        <td>{{ $row->product?->description }}</td>
                                        <td>{{ number_format((float) $row->qty_available, 2) }}</td>
                                        <td>{{ number_format((float) $row->qty_reserved, 2) }}</td>
                                        <td>{{ number_format($free, 2) }}</td>
                                        <td>
                                            @if($free < $safety)
                                                <span class="tag tag-critical">Below safety</span>
                                            @else
                                                <span class="tag tag-ok">Healthy</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6">No inventory balance data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="bom" class="panel">
                <div class="panel-header">
                    <h2>BOM Explorer</h2>
                    <small>Versioned structures and component depth snapshot</small>
                </div>
                <div class="panel-body table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>BOM</th>
                                <th>Product</th>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Items</th>
                                <th>Effective</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bom['headers'] as $header)
                                <tr>
                                    <td>#{{ $header->id }}</td>
                                    <td>{{ $header->product?->sku }} — {{ $header->product?->description }}</td>
                                    <td>v{{ $header->version_number }}</td>
                                    <td>{{ $header->status }}</td>
                                    <td>{{ $header->items_count }}</td>
                                    <td>{{ optional($header->effective_from)->toDateString() ?? '-' }} → {{ optional($header->effective_to)->toDateString() ?? 'open' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6">No BOM versions registered.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="genealogy" class="panel">
                <div class="panel-header">
                    <h2>Genealogy Explorer</h2>
                    <small>Forward and backward trace relation graph preview</small>
                </div>
                <div class="panel-body table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Relation</th>
                                <th>Parent Node</th>
                                <th>Child Node</th>
                                <th>Qty</th>
                                <th>UoM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($genealogy['relations'] as $relation)
                                <tr>
                                    <td>{{ $relation->relation_type }}</td>
                                    <td>{{ $relation->parentNode?->node_type }} / {{ $relation->parentNode?->source_reference ?? ('#'.$relation->parent_node_id) }}</td>
                                    <td>{{ $relation->childNode?->node_type }} / {{ $relation->childNode?->source_reference ?? ('#'.$relation->child_node_id) }}</td>
                                    <td>{{ number_format((float) $relation->quantity, 2) }}</td>
                                    <td>{{ $relation->uom ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5">No genealogy relations available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="gantt" class="panel">
                <div class="panel-header">
                    <h2>Scheduling Gantt</h2>
                    <small>Finite schedule horizon: {{ $today->toDateString() }} to {{ $windowEnd->toDateString() }}</small>
                </div>
                <div class="panel-body">
                    <div class="kpi-grid" style="grid-template-columns: repeat(2,minmax(0,1fr)); margin-bottom: .8rem;">
                        <div class="kpi"><div class="label">Planned Operations Window</div><div class="value">{{ count($scheduling['gantt_rows']) }}</div></div>
                        <div class="kpi"><div class="label">Working Calendar Slots</div><div class="value">{{ count($scheduling['calendar_rows']) }}</div></div>
                    </div>

                    @if(count($scheduling['gantt_rows']) === 0)
                        <div class="empty">No scheduled production orders in the selected window.</div>
                    @else
                        <div class="gantt">
                            <div class="gantt-head">Timeline scale: 30-day window</div>
                            @foreach($scheduling['gantt_rows'] as $bar)
                                <div class="gantt-row">
                                    <div class="gantt-label">
                                        <strong>{{ $bar['order_number'] }}</strong>
                                        <span class="tag">{{ $bar['status'] }}</span>
                                    </div>
                                    <div class="gantt-track">
                                        <div class="gantt-bar" style="left: {{ $bar['left_percent'] }}%; width: {{ $bar['width_percent'] }}%;">
                                            {{ $bar['product_sku'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </main>
    </div>
</body>
</html>