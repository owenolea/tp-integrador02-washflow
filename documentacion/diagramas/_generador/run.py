import sys, importlib
OUTDIR = sys.argv.pop(1) if len(sys.argv) > 1 and '/' in sys.argv[1] else '../'
mods = sys.argv[1:] or ['d01']
for m in mods:
    mod = importlib.import_module(m)
    ds = mod.build()
    ds = ds if isinstance(ds, list) else [ds]
    for d in ds:
        d.save(OUTDIR); print('ok', d.name)
