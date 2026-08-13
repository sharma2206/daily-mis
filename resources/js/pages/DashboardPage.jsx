import React, { useState, useEffect, useRef } from 'react';
import Navbar from '../components/Navbar';

const BED = { chromepet: 74, oragadam: 14 };
const REV_COLS = ['op', 'ip', 'er', 'ph'];

function lk(v) { return ((v || 0) / 100000).toFixed(2); }
function fmtRupee(v) { return '₹' + Math.round(Number(v || 0)); }

export default function DashboardPage() {
    const today = new Date().toISOString().split('T')[0];
    const [branch, setBranch] = useState('chromepet');
    const [date, setDate] = useState(today);
    const [reportData, setReportData] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const branchRef = useRef(branch);
    branchRef.current = branch;

    // ── switchBranch ─────────────────────────────────────────────
    function switchBranch(b) {
        setBranch(b);
        if (reportData) loadReport(b, date);
    }

    // ── shiftDate ────────────────────────────────────────────────
    function shiftDate(d) {
        const dt = new Date(date);
        dt.setDate(dt.getDate() + d);
        const s = dt.toISOString().split('T')[0];
        if (s <= today) {
            setDate(s);
            loadReport(branch, s);
        }
    }

    // ── loadReport ───────────────────────────────────────────────
    async function loadReport(br, dt) {
        const b = br || branch;
        const d = dt || date;
        if (!d) return;
        setLoading(true);
        setError(null);
        try {
            const r = await fetch(`/api/mis/${b}/${d}`);
            const j = await r.json();
            if (j.success) {
                setReportData(j.data);
            } else {
                setReportData(null);
                setError(j.message || 'No data found');
            }
        } catch (e) {
            setError('Network error: ' + e.message);
        } finally {
            setLoading(false);
        }
    }

    return (
        <>
            <Navbar badge="Dashboard" />
            <div className="main">
                {error && (
                    <div className="alert alert-error">
                        <span className="material-icons-round" style={{ fontSize: 18 }}>error_outline</span>
                        <span>{error}</span>
                        <span className="close-a" onClick={() => setError(null)}>&times;</span>
                    </div>
                )}

                <div className="controls">
                    <div className="branch-pills">
                        {['chromepet', 'oragadam'].map(b => (
                            <button key={b} className={`branch-pill${branch === b ? ' active' : ''}`}
                                onClick={() => switchBranch(b)}>
                                {b === 'chromepet' ? 'Chromepet' : 'Oragadam'}
                            </button>
                        ))}
                    </div>
                    <div className="date-control">
                        <button className="date-nav" onClick={() => shiftDate(-1)} title="Previous day">
                            <span className="material-icons-round" style={{ fontSize: 16 }}>chevron_left</span>
                        </button>
                        <input type="date" className="date-input" id="reportDate" max={today}
                            value={date} onChange={e => setDate(e.target.value)} />
                        <button className="date-nav" onClick={() => shiftDate(1)} title="Next day">
                            <span className="material-icons-round" style={{ fontSize: 16 }}>chevron_right</span>
                        </button>
                        <button className="btn-load" id="loadBtn" onClick={() => loadReport()} disabled={loading}>
                            <span className="material-icons-round" style={{ fontSize: 16 }}>refresh</span>
                            <span>{loading ? 'Loading...' : 'Load Report'}</span>
                        </button>
                    </div>
                </div>

                <div id="content">
                    {loading && (
                        <div className="loading-overlay show">
                            <div className="loader" />
                        </div>
                    )}
                    {!reportData && !loading && !error && (
                        <div className="no-data">
                            <div className="nd-icon"><span className="material-icons-round">analytics</span></div>
                            <p>Select a branch and date to view MIS report</p>
                            <p className="sub">Upload CSV files first if no data exists for the selected date</p>
                        </div>
                    )}
                    {!reportData && !loading && error && (
                        <div className="no-data">
                            <div className="nd-icon"><span className="material-icons-round">warning</span></div>
                            <p>{error}</p>
                            <p className="sub">Try uploading CSV files for this date first</p>
                        </div>
                    )}
                    {reportData && <Report data={reportData} branch={branch} date={date} />}
                </div>
            </div>
        </>
    );
}

// ── Report renderer ──────────────────────────────────────────────
function Report({ data, branch, date }) {
    const s = data.sales || {}, c = data.collection || {}, dc = data.discount || {};
    const r = data.refund || {}, v = data.volume || {}, m = data.mri || {};
    const t = data.totals || {}, pkg = data.pkg_adjustment || { ftd: 0, mtd: 0 };
    const isChromepet = branch === 'chromepet';
    const isOragadam = branch === 'oragadam';
    const colSpan = REV_COLS.length + 1;

    // ── Summary Cards
    const summaryCards = [
        {
            cls: 'sales', icon: 'payments', label: 'Total Sales',
            ftd: `₹${lk(t.sales_ftd)}`, mtd: `₹${lk(t.sales_mtd)}`, unit: 'Lakhs'
        },
        {
            cls: 'collection', icon: 'account_balance', label: 'Collection',
            ftd: `₹${lk(t.collection_ftd)}`, mtd: `₹${lk(t.collection_mtd)}`, unit: 'Lakhs'
        },
        {
            cls: 'discount', icon: 'hotel', label: 'Occupancy',
            ftd: v.ftd?.occupancy || 0, ftdUnit: `of ${BED[branch]} beds`,
            mtd: `${Number(v.ftd?.occupancy_pct || 0).toFixed(1)}%`, mtdLabel: 'FTD %', unit: 'Occupancy'
        },
        {
            cls: 'refund', icon: 'person_add', label: 'Admissions',
            ftd: v.ftd?.admission || 0, mtd: v.mtd?.admission || 0,
            ftdUnit: 'Admitted', unit: 'Total'
        },
    ];

    // ── Revenue table rows
    const tableRows = [
        { label: 'Sales', d: s },
        { label: 'Collection', d: c },
    ];
    const dp = dc.ftd?.partial || {}, dpm = dc.mtd?.partial || {};
    const df = dc.ftd?.full || {}, dfm = dc.mtd?.full || {};
    const rf = r.ftd || {}, rm = r.mtd || {};

    // ── Volume cards
    const vols = [
        { label: 'Occupancy', ftd: v.ftd?.occupancy || 0, mtd: v.mtd?.occupancy || 0 },
        { label: 'Occupancy %', ftd: (v.ftd?.occupancy_pct || 0) + '%', mtd: (v.mtd?.occupancy_pct || 0) + '%' },
        { label: 'Admission', ftd: v.ftd?.admission || 0, mtd: v.mtd?.admission || 0 },
        { label: 'Discharge', ftd: v.ftd?.discharge || 0, mtd: v.mtd?.discharge || 0 },
        { label: 'Total OP', ftd: v.ftd?.total_op || 0, mtd: v.mtd?.total_op || 0 },
    ];
    if (isOragadam) vols.push({ label: 'Total ER', ftd: v.ftd?.er_count || 0, mtd: v.mtd?.er_count || 0 });
    if (isChromepet) {
        vols.push(
            { label: 'MRI OP (Count)', ftd: m.ftd?.op?.count || 0, mtd: m.mtd?.op?.count || 0 },
            { label: 'MRI IP (Count)', ftd: m.ftd?.ip?.count || 0, mtd: m.mtd?.ip?.count || 0 },
            { label: 'MRI OP Revenue', ftd: fmtRupee(m.ftd?.op?.revenue), mtd: fmtRupee(m.mtd?.op?.revenue), wide: true },
            { label: 'MRI IP Revenue', ftd: fmtRupee(m.ftd?.ip?.revenue), mtd: fmtRupee(m.mtd?.ip?.revenue), wide: true },
        );
    }

    function revRow(label, ft, mt) {
        const ftT = REV_COLS.reduce((a, k) => a + (ft[k] || 0), 0);
        const mtT = REV_COLS.reduce((a, k) => a + (mt[k] || 0), 0);
        return (
            <tr key={label}>
                <td>{label}</td>
                {REV_COLS.map(k => <td key={k}>{lk(ft[k])}</td>)}
                <td className="total-col ftd-total">{lk(ftT)}</td>
                {REV_COLS.map(k => <td key={k + 'm'}>{lk(mt[k])}</td>)}
                <td className="total-col mtd-total">{lk(mtT)}</td>
            </tr>
        );
    }

    return (
        <>
            {/* Summary */}
            <div className="summary-grid">
                {summaryCards.map(sc => (
                    <div key={sc.cls} className={`summary-card ${sc.cls}`}>
                        <div className="sc-icon"><span className="material-icons-round">{sc.icon}</span></div>
                        <div className="sc-label">{sc.label}</div>
                        <div className="sc-row">
                            <div className="sc-block">
                                <div className="sc-period">FTD</div>
                                <div className="sc-value ftd">{sc.ftd}</div>
                                <div className="sc-unit">{sc.ftdUnit || sc.unit}</div>
                            </div>
                            <div className="sc-block">
                                <div className="sc-period">{sc.mtdLabel || 'MTD'}</div>
                                <div className="sc-value mtd">{sc.mtd}</div>
                                <div className="sc-unit">{sc.unit}</div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Package alert */}
            {((pkg.ftd || 0) > 0 || (pkg.mtd || 0) > 0) && (
                <div className="pkg-alert">
                    <span className="material-icons-round">inventory_2</span>
                    <span>Package Adjustment (Chromepet): Added to Pharmacy, subtracted from IP</span>
                    <div className="pkg-vals">
                        <span>FTD: ₹{lk(pkg.ftd)} L</span>
                        <span>MTD: ₹{lk(pkg.mtd)} L</span>
                    </div>
                </div>
            )}

            {/* Revenue table */}
            <div className="section">
                <div className="section-head">
                    <div className="section-title">
                        <span className="material-icons-round">table_chart</span>
                        Revenue Breakdown <span className="sub">(₹ in Lakhs)</span>
                    </div>
                    <div className="export-btns">
                        <a className="btn-export" href={`/api/mis/${branch}/${date}/export`} target="_blank" rel="noreferrer">
                            <span className="material-icons-round" style={{ fontSize: 14 }}>download</span> Excel
                        </a>
                        <a className="btn-export pdf" href={`/api/mis/${branch}/${date}/export-pdf`} target="_blank" rel="noreferrer">
                            <span className="material-icons-round" style={{ fontSize: 14 }}>picture_as_pdf</span> PDF
                        </a>
                    </div>
                </div>
                <div className="card">
                    <table className="tbl">
                        <thead>
                            <tr className="super-header">
                                <th></th>
                                <th colSpan={colSpan} className="ftd-h">FTD ({date})</th>
                                <th colSpan={colSpan} className="mtd-h">MTD</th>
                            </tr>
                            <tr>
                                <th>Category</th>
                                {REV_COLS.map(k => <th key={k}>{k.toUpperCase()}</th>)}
                                <th>Total</th>
                                {REV_COLS.map(k => <th key={k + 'm'}>{k.toUpperCase()}</th>)}
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {tableRows.map(rw => revRow(rw.label, rw.d.ftd || {}, rw.d.mtd || {}))}
                            {revRow('Discount 99%', dp, dpm)}
                            {revRow('Discount 100%', df, dfm)}
                            {revRow('Refund', rf, rm)}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Volume */}
            <div className="section">
                <div className="section-head">
                    <div className="section-title">
                        <span className="material-icons-round">trending_up</span>
                        {isChromepet ? 'Volume Indicators & MRI' : 'Volume Indicators'}
                    </div>
                </div>
                <div className="vol-grid">
                    {vols.map((vi, i) => (
                        <div key={i} className={`vol-card${vi.wide ? ' wide' : ''}`}>
                            <div className="vol-label">{vi.label}</div>
                            <div className="vol-values">
                                <div className="vol-item">
                                    <div className="vol-period">FTD</div>
                                    <div className={`vol-num ftd${vi.wide ? ' sm' : ''}`}>{vi.ftd}</div>
                                </div>
                                <div className="vol-item">
                                    <div className="vol-period">MTD</div>
                                    <div className={`vol-num mtd${vi.wide ? ' sm' : ''}`}>{vi.mtd}</div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </>
    );
}
