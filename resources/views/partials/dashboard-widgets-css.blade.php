{{-- Shared widget styles: solid-color small-box KPI cards and icon-led
     info-boxes, matching the e-ManExcel reliability-dashboard reference.
     Include this once via @section('css') on any page using those classes
     (small-box-custom / sb-*, info-box-custom / ib-*). --}}
<style>
.small-box-custom {
    position: relative;
    border-radius: 12px;
    color: #fff;
    margin-bottom: 1rem;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(15,23,42,.1);
    transition: transform .15s, box-shadow .15s;
    display: block;
}
.small-box-custom:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(15,23,42,.18); }
.small-box-custom .sb-inner { position: relative; z-index: 1; padding: 1.1rem 1.1rem .9rem; }
.small-box-custom h3 { font-size: 1.9rem; font-weight: 700; margin: 0; line-height: 1.05; }
.small-box-custom p { margin: .3rem 0 0; font-size: .85rem; font-weight: 500; opacity: .95; }
.small-box-custom .sb-sub { margin-top: .35rem; font-size: .72rem; opacity: .85; }
.small-box-custom .sb-icon {
    position: absolute; top: .3rem; right: .6rem;
    font-size: 3.4rem; opacity: .25; line-height: 1;
}
.small-box-custom .sb-footer {
    display: block; position: relative; z-index: 1;
    padding: .5rem 1.1rem;
    background: rgba(0,0,0,.13);
    color: #fff; font-size: .76rem; font-weight: 600;
    text-decoration: none;
}
.small-box-custom .sb-footer:hover { background: rgba(0,0,0,.24); color: #fff; text-decoration: none; }

.sb-teal   { background: #0d9488; }
.sb-blue   { background: #2563eb; }
.sb-green  { background: #16a34a; }
.sb-purple { background: #7c3aed; }
.sb-orange { background: #d97706; }
.sb-red    { background: #dc2626; }

.info-box-custom {
    background: #fff;
    border: 1px solid #e6e9ef;
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: .9rem;
    box-shadow: 0 1px 3px rgba(15,23,42,.05);
    margin-bottom: 1rem;
}
.info-box-custom .ib-icon {
    width: 48px; height: 48px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.info-box-custom .ib-body { display: flex; flex-direction: column; min-width: 0; }
.info-box-custom .ib-label { font-size: .74rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
.info-box-custom .ib-value { font-size: 1.35rem; font-weight: 700; color: #0f2942; line-height: 1.25; }
.info-box-custom .ib-sub { font-size: .74rem; color: #94a3b8; }
</style>
