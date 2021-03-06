<div>
    <a class="btn btn-outline-primary btn-sm" href="<?= ROOT ?>/etudiant/nouveau">
        <span><i class="fas fa-plus"></i></span>
        Ajouter
    </a>
</div>
<div>
    <form class="d-flex justify-content-end mt-2" action="" method="post" id="formSearch">
        <div class="row align-items-center">
            <div class="col-8 d-flex justify-content-end">
                <input type="text" name="mat" id="mat" class="form-control mb-2 mr-2" placeholder="Matricule">
                <input type="text" name="num" id="num" class="form-control mb-2 mr-2" placeholder="Num chambre">
                <select name="type" id="type"  class="form-control mb-2 mr-2">
                    <option value="" selected disabled>Type</option>
                    <option value="non_boursier">Non Boursier</option>
                    <option value="boursier">Boursier</option>
                    <option value="logier">Logier</option>
                    <option value="non_logier">Non Logier</option>
                </select>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-primary btn-sm mb-2 mr-2">Chercher</button>
            </div>
        </div>
    </form>
</div>
<div class="col-11">
    <div class="table-responsive" id="scrollZone">
        <table class="table table-bordered table-hover text-center">
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Prenom</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Bourse</th>
                    <th>Chambre</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tbody">

            </tbody>
        </table>
    </div>
</div>

<script>
    let text = '';
    $(document).ready(function(){
        let offset = 0;
        let mat = "";
        let num = "";
        let type = "";
        const tbody = $("#tbody");
        $.ajax({
            url: '<?= ROOT ?>/etudiant/showAll',
            type: 'POST',
            data: {limit: 2, offset: offset, mat: mat, num: num, type: type},
            beforeSend: function(){
                tbody.html('<tr><td colspan="8">Loading...</td></tr>')
            },
            success: function(res){
                tbody.html('')
                if(res != ''){
                    tbody.html(res);
                    offset += 2;
                }else{
                    tbody.html('<tr><td colspan="8">Not Found</td></tr>');
                }
            }
        });
        
        const scrollZone = $('#scrollZone')
        scrollZone.scroll(() => {
            const st = scrollZone[0].scrollTop;
            const sh = scrollZone[0].scrollHeight;
            const ch = scrollZone[0].clientHeight;


            if(Math.ceil(sh-st)-1 <= ch){
                $.ajax({
                    url: '<?= ROOT ?>/etudiant/showAll',
                    type: 'POST',
                    data: {limit: 2, offset: offset, mat: mat, num: num, type: type},
                    success: function(res){
                        tbody.append(res);
                        offset += 2;
                    }
                });
            }
        })

        $("#formSearch").on("submit", function(e){
            e.preventDefault();
            offset = 0;
            mat = $("#mat").val();
            num = $("#num").val();
            type = $("#type").val();
            $.ajax({
                url: '<?= ROOT ?>/etudiant/showAll',
                type: 'POST',
                data: {limit: 2, offset: offset, mat: mat, num: num, type: type},
                beforeSend: function(){
                    tbody.html('<tr><td colspan="8">Loading...</td></tr>')
                },
                success: function(res){
                    tbody.html('')
                    if(res != ''){
                        tbody.html(res);
                        offset += 2;
                    }else{
                        tbody.html('<tr><td colspan="8">Not Found</td></tr>');
                    }
                }
            });
        });
    });

    $(document).on("click", ".deleteStud", function(){
        if(confirm("Voulez-vous supprimer")){
            $.ajax({
                url: '<?= ROOT ?>/etudiant/deleteStud',
                type: 'POST',
                data: {id: $(this).attr("id")},
                dataType: 'JSON',
                success: function(res){
                    if(res.type == 'success'){
                        alert(res.message);
                        location.reload();
                    }
                }
            })
        }
    });

    $(document).on('dblclick', '.edit', function(e){
        e.preventDefault();
        $(this).prop('disabled', true);
        text = $(this).text();
        const id = $(this).parent().attr("id");
        const name = $(this).attr("id");
        const input = `<input class="form-control w-100" type="text" id="inputChange" data-id="${id}" name="${name}" value="${text}" placeholder="Entrer ${name}">`; 
        $(this).html(input);
        $(this).children().focus();
    })
    $(document).on('focusout', '.edit', function(){
        $(this).html(text);
    });
    $(document).on('keyup', ".edit", function(e){
        if(e.keyCode == 27){ // Touche echap
            $(this).html(text);
        }else if(e.keyCode == 13){ // Touche entrée
            const data = {
                "id": $("#inputChange").attr("data-id"),
                "champ": $(this).attr("id"),
                "val": $("#inputChange").val()
            };
            if(data.val == ''){
                $("#inputChange").addClass("is-invalid");
                return;
            }else if(data.champ == 'nom' && !validWithRegex(data.val, /^[A-Za-z]*$/)){
                $("#inputChange").addClass("is-invalid");
                return;
            }else if(data.champ == 'prenom' && !validWithRegex(data.val, /^[A-Za-z ]*$/)){
                $("#inputChange").addClass("is-invalid");
                return;
            }else if(data.champ == 'email' && !validWithRegex(data.val, /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/)){
                $("#inputChange").addClass("is-invalid");
                return;
            }else if(data.champ == 'tel' && !validWithRegex(data.val, /(7[7860])+([0-9]{7})$/)){
                $("#inputChange").addClass("is-invalid");
                return;
            }else{
                $.ajax({
                    url: '<?= ROOT ?>/etudiant/updateEtudiant',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(res){
                        if(res.type == "success"){
                            alert(res.message);
                        }else{
                            alert(res.message);
                            return;
                        }        
                    }
                });
                $(this).html($("#inputChange").val());
            }
        }
    })
    
</script>

       