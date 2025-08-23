<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Database</title>
</head>

<body>
    <script>
        async function fetchData(url) {
            return await fetch(url)
                .then(r => r.json())
                .then(d => { return d; })
                .catch(err => {
                    console.error("Error fetching", err);
                });
        }

        let main_data = [];
        let source_data = [];

        async function checkDatabase() {
            await fetchData('main/1.json?v=' + Math.round(Math.random() * 100)).then(d => {
                main_data = d;
            })
            await fetchData('main/2.json?v=' + Math.round(Math.random() * 100)).then(d => {
                main_data = main_data.concat(d);
            })

            console.log("- Checking element id order -");
            let id_order_errors = 0;
            for (let i = 0; i < main_data.length; i++) {
                if (+(main_data[i].movie_id) !== (i + 1)) {
                    id_order_errors++;
                    console.log("Not found movie_id:", (i + 1));
                }
            }
            if (id_order_errors == 0) {
                console.log("No errors found in movie_id order");
            }

            await fetchData('vid-src/1.json?v=' + Math.round(Math.random() * 100)).then(d => {
                source_data = d;
            })
            await fetchData('vid-src/2.json?v=' + Math.round(Math.random() * 100)).then(d => {
                source_data = source_data.concat(d);
            })
            await fetchData('vid-src/3.json?v=' + Math.round(Math.random() * 100)).then(d => {
                source_data = source_data.concat(d);
            })

            console.log("- Checking sources -");
            let source_errors = 0;
            main_data.forEach(e => {
                let f = source_data.find(i => i.movie_id === e.movie_id);
                if (!f) {
                    console.log("Not Found:", e.name, e.movie_id);
                    source_errors++;
                }
            });
            if (source_errors > 0) {
                console.log("Source errors:", source_errors);
            } else {
                console.log("No errors found in sources");
            }
        }

        checkDatabase();
    </script>
</body>

</html>