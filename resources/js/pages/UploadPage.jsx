import React, { useState, useRef, useCallback } from "react";
import Navbar from "../components/Navbar";

const BED_COUNTS = { chromepet: 74, oragadam: 14 };

export default function UploadPage() {
    const [branch, setBranch] = useState("chromepet");
    const [occupancy, setOccupancy] = useState("");
    const [occupancyPct, setOccupancyPct] = useState("—");
    const [occupancyPctVal, setOccupancyPctVal] = useState(0);
    const [files, setFiles] = useState({
        bill_file: null,
        cashier_file: null,
        package_file: null,
    });
    const [alert, setAlert] = useState(null); // { type: 'success'|'error', msg }
    const [loading, setLoading] = useState(false);
    const [response, setResponse] = useState(null);
    const formRef = useRef(null);

    const today = new Date().toISOString().split("T")[0];

    // ── calcOccupancyPct ──────────────────────────────────────────
    const calcOccupancyPct = useCallback(
        (occ, br) => {
            const beds = BED_COUNTS[br || branch];
            const val = parseInt(occ) || 0;
            const pct = beds > 0 ? ((val / beds) * 100).toFixed(2) : 0;
            setOccupancyPct(val > 0 ? pct + "%" : "—");
            setOccupancyPctVal(pct);
        },
        [branch],
    );

    // ── selectBranch ──────────────────────────────────────────────
    function selectBranch(b) {
        setBranch(b);
        if (b !== "chromepet") {
            setFiles((f) => ({ ...f, package_file: null }));
        }
        calcOccupancyPct(occupancy, b);
    }

    // ── onFile ───────────────────────────────────────────────────
    function onFile(name, file) {
        setFiles((f) => ({ ...f, [name]: file || null }));
    }

    // ── resetForm ────────────────────────────────────────────────
    function resetForm() {
        formRef.current?.reset();
        setOccupancy("");
        setOccupancyPct("—");
        setOccupancyPctVal(0);
        setFiles({ bill_file: null, cashier_file: null, package_file: null });
        setAlert(null);
        setResponse(null);
        selectBranch("chromepet");
    }

    // ── submit ───────────────────────────────────────────────────
    async function handleSubmit(e) {
        e.preventDefault();
        setAlert(null);
        setLoading(true);
        const fd = new FormData(formRef.current);
        fd.append("branch", branch);
        fd.set("occupancy_pct", occupancyPctVal);
        if (branch !== "chromepet") fd.delete("package_file");
        try {
            const r = await fetch("/api/mis/" + branch + "/upload", {
                method: "POST",
                body: fd,
            });
            const j = await r.json();
            if (j.success) {
                setAlert({ type: "success", msg: j.message || "Done!" });
                setResponse(j);
            } else {
                setAlert({
                    type: "error",
                    msg:
                        j.message ||
                        (j.errors
                            ? Object.values(j.errors).flat().join(", ")
                            : "Failed."),
                });
            }
        } catch (err) {
            setAlert({ type: "error", msg: "Network error: " + err.message });
        } finally {
            setLoading(false);
        }
    }

    // ── showResponse stats ───────────────────────────────────────
    function renderStats(j) {
        const imp = j.imported || {};
        const d = j.data || {},
            s = d.sales || {},
            ftd = s.ftd || {};
        const tot = (
            (ftd.op || 0) +
            (ftd.ip || 0) +
            (ftd.er || 0) +
            (ftd.ph || 0)
        ).toFixed(2);
        return (
            <>
                <div className="stat-box">
                    <div className="stat-label">Bill Items</div>
                    <div className="stat-value">
                        {(imp.bill_items || 0).toLocaleString()}
                    </div>
                </div>
                <div className="stat-box">
                    <div className="stat-label">Collections</div>
                    <div className="stat-value">
                        {(imp.cashier_collections || 0).toLocaleString()}
                    </div>
                </div>
                {branch === "chromepet" && (
                    <div className="stat-box">
                        <div className="stat-label">Packages</div>
                        <div className="stat-value">
                            {(imp.package_consumptions || 0).toLocaleString()}
                        </div>
                    </div>
                )}
                <div className="stat-box">
                    <div className="stat-label">FTD Sales</div>
                    <div className="stat-value">
                        ₹{Number(tot).toLocaleString()}
                    </div>
                </div>
            </>
        );
    }

    const dateVal = formRef.current?.querySelector("#dateInput")?.value || "";
    const isChromepet = branch === "chromepet";

    return (
        <>
            <Navbar badge="v1.0" />
            <div className="upload-container">
                <div className="intro">
                    <h2>MIS Report Upload</h2>
                    <p>
                        Upload hospital branch daily transaction logs to
                        generate MIS analytics
                    </p>
                </div>

                {alert && (
                    <div className={`alert alert-${alert.type}`}>
                        <span className="material-icons-round">
                            {alert.type === "success"
                                ? "check_circle"
                                : "error"}
                        </span>
                        <span className="msg">{alert.msg}</span>
                        <span
                            className="close-a"
                            onClick={() => setAlert(null)}
                        >
                            &times;
                        </span>
                    </div>
                )}

                {/* Branch Tabs */}
                <div className="branch-tabs">
                    {["chromepet", "oragadam"].map((b) => (
                        <div
                            key={b}
                            className={`branch-tab${branch === b ? " active" : ""}`}
                            onClick={() => selectBranch(b)}
                        >
                            <div className="branch-icon">
                                <span className="material-icons-round">
                                    {b === "chromepet"
                                        ? "local_hospital"
                                        : "domain"}
                                </span>
                            </div>
                            <div>
                                <div className="name">
                                    {b === "chromepet"
                                        ? "Chromepet"
                                        : "Oragadam"}
                                </div>
                                <div className="info">
                                    Beds: <span>{BED_COUNTS[b]}</span> ·{" "}
                                    {b === "chromepet" ? "3" : "2"} files
                                    required
                                </div>
                            </div>
                            <span className="check-icon">check_circle</span>
                        </div>
                    ))}
                </div>

                <form
                    id="uploadForm"
                    ref={formRef}
                    onSubmit={handleSubmit}
                    encType="multipart/form-data"
                >
                    {/* Date */}
                    <div className="upload-card">
                        <div className="card-title">
                            <span className="material-icons-round">
                                calendar_today
                            </span>
                            Report Date
                        </div>
                        <div className="form-row single">
                            <div className="form-group">
                                <label>
                                    Date <span className="req">*</span>
                                </label>
                                <input
                                    type="date"
                                    name="date"
                                    id="dateInput"
                                    max={today}
                                    required
                                />
                                <span className="help">
                                    Select the date of transaction records
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Files */}
                    <div className="upload-card">
                        <div className="card-title">
                            <span className="material-icons-round">
                                folder_open
                            </span>
                            CSV / Excel Files
                        </div>
                        <div className="form-row">
                            <FileZone
                                name="bill_file"
                                label="Bill Item File"
                                icon="description"
                                title="Bill Item Report"
                                file={files.bill_file}
                                onFile={onFile}
                                required
                            />
                            <FileZone
                                name="cashier_file"
                                label="Cashier Collection File"
                                icon="payments"
                                title="Cashier Collection"
                                file={files.cashier_file}
                                onFile={onFile}
                                required
                            />
                        </div>
                        <div
                            className={`package-section${isChromepet ? " visible" : " hidden"}`}
                        >
                            <div className="form-row single">
                                <FileZone
                                    name="package_file"
                                    label="Package Consumption File"
                                    icon="card_membership"
                                    title="Package Consumption"
                                    file={files.package_file}
                                    onFile={onFile}
                                    required={isChromepet}
                                    hint="Chromepet only"
                                    showReq={isChromepet}
                                />
                            </div>
                        </div>
                    </div>

                    {/* Volume */}
                    <div className="upload-card">
                        <div className="card-title">
                            <span className="material-icons-round">
                                leaderboard
                            </span>
                            Volume Indicators (FTD)
                        </div>
                        <div className="form-row">
                            <div className="form-group">
                                <label>Occupancy (Beds Occupied)</label>
                                <input
                                    type="number"
                                    name="occupancy"
                                    id="occupancyInput"
                                    min="0"
                                    placeholder="e.g. 52"
                                    value={occupancy}
                                    onChange={(e) => {
                                        setOccupancy(e.target.value);
                                        calcOccupancyPct(e.target.value);
                                    }}
                                />
                            </div>
                            <div className="form-group">
                                <label>Occupancy %</label>
                                <div className="calc-value">{occupancyPct}</div>
                                <input
                                    type="hidden"
                                    name="occupancy_pct"
                                    value={occupancyPctVal}
                                />
                                <span className="help">
                                    Auto: (Occupancy ÷{" "}
                                    <span id="bedCountLabel">
                                        {BED_COUNTS[branch]}
                                    </span>
                                    ) × 100
                                </span>
                            </div>
                        </div>
                        <div className="form-row">
                            <div className="form-group">
                                <label>Admission</label>
                                <input
                                    type="number"
                                    name="admission"
                                    min="0"
                                    placeholder="0"
                                />
                            </div>
                            <div className="form-group">
                                <label>Discharge</label>
                                <input
                                    type="number"
                                    name="discharge"
                                    min="0"
                                    placeholder="0"
                                />
                            </div>
                        </div>
                        {branch === "oragadam" && (
                            <div className="form-row single">
                                <div className="form-group">
                                    <label>
                                        ER Count (Manual Entry){" "}
                                        <span className="req">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        name="er_count"
                                        id="erCountInput"
                                        min="0"
                                        placeholder="0"
                                        required
                                    />
                                    <span className="help">
                                        Enter the ER count for this day
                                        (Oragadam only)
                                    </span>
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="submit-row">
                        <button
                            type="submit"
                            className="btn btn-primary"
                            id="submitBtn"
                            disabled={loading}
                        >
                            {loading ? (
                                <>
                                    <div
                                        className="spinner"
                                        style={{ display: "block" }}
                                    />
                                    <span>Processing...</span>
                                </>
                            ) : (
                                <span>Upload &amp; Generate MIS</span>
                            )}
                        </button>
                        <button
                            type="button"
                            className="btn btn-reset"
                            onClick={resetForm}
                        >
                            Reset
                        </button>
                    </div>
                </form>

                {response && (
                    <div className="upload-card response-card show">
                        <div className="card-title">
                            <span className="material-icons-round">
                                assignment_turned_in
                            </span>
                            Import Summary
                        </div>
                        <div className="response-grid">
                            {renderStats(response)}
                        </div>
                        <div
                            style={{
                                display: "flex",
                                gap: ".75rem",
                                flexWrap: "wrap",
                            }}
                        >
                            <a
                                href={`/api/mis/${branch}/${response.date || dateVal}/export`}
                                className="btn btn-primary"
                                style={{
                                    textDecoration: "none",
                                    fontSize: ".82rem",
                                    padding: ".55rem 1.1rem",
                                    flex: "none",
                                }}
                            >
                                <span
                                    className="material-icons-round"
                                    style={{ fontSize: "18px" }}
                                >
                                    table_view
                                </span>{" "}
                                Download Excel
                            </a>
                            <a
                                href={`/api/mis/${branch}/${response.date || dateVal}/export-pdf`}
                                className="btn"
                                style={{
                                    textDecoration: "none",
                                    fontSize: ".82rem",
                                    padding: ".55rem 1.1rem",
                                    background:
                                        "linear-gradient(135deg,#ef4444,#dc2626)",
                                    color: "#fff",
                                    borderRadius: "10px",
                                    fontWeight: 600,
                                }}
                            >
                                <span
                                    className="material-icons-round"
                                    style={{ fontSize: "18px" }}
                                >
                                    picture_as_pdf
                                </span>{" "}
                                Download PDF
                            </a>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

// ── FileZone sub-component ────────────────────────────────────────
function FileZone({
    name,
    label,
    icon,
    title,
    file,
    onFile,
    required,
    hint,
    showReq = true,
}) {
    function handleChange(e) {
        const f = e.target.files?.[0] || null;
        onFile(name, f);
    }
    return (
        <div className="form-group">
            <label>
                {label}{" "}
                {showReq !== false && required && (
                    <span className="req">*</span>
                )}
            </label>
            <div className={`file-zone${file ? " selected" : ""}`}>
                <span className={`material-icons-round zi`}>{icon}</span>
                <span className="zt">{title}</span>
                <span className="zs">Click to browse (.csv, .xlsx)</span>
                <input
                    type="file"
                    name={name}
                    accept=".csv,.txt,.xlsx,.xls"
                    required={required}
                    onChange={handleChange}
                />
            </div>
            {file && (
                <span className="fbadge show">
                    <span className="material-icons-round">task_alt</span>
                    <span className="fn">{file.name}</span>
                </span>
            )}
            {hint && <span className="help">{hint}</span>}
        </div>
    );
}
