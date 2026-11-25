// SCRIPTS PARA OTORGAR PERMISOS A USUARIOS PARA EDITAR EL COMITE
var columnaVisible = (statusComite === 0);
var tablaMaterial = $("#tabla_material").DataTable({
  dom: "Brtip",
  ajax: {
    url: "/funciones/aspectos_func",
    type: "POST",
    data: {
      action: "listar-materialE",
      id_comite: id_comite,
    },
    dataSrc: function(json) {
      $("#total_material").val(json.total_material ? json.total_material : 0);
      return json.data;
    },
  },
  autoWidth: false,
  columnDefs: [
    {
      targets: 0, // Índice de la columna a ocultar (0 = primera columna)
      visible: false, // La oculta en la tabla
      searchable: false, // Opcional: evita que se busque en esa columna
    },
    { width: "40%", targets: 1 },
    { width: "40%", targets: 2 },
    { width: "20%", targets: 3, visible: columnaVisible  },
  ],
  paging: false,
  processing: true,
  serverSide: false,
  language: {
    sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
    sLoadingRecords: "<div style='height:150px'></div>",
    zeroRecords: "Material de difusión no registrado",
  },
});

$("#btn-agregar-material").on("click", function () {
  var idMaterial = $("#select-material").val();
  var idComite = $("#id_comite").val();
  var cantidad = $("#cantidad").val();

  if (!idMaterial) {
    mostrarAlerta("warning", "Por favor seleccione un material.");
    return;
  }

  if (!cantidad || cantidad <= 0) {
    mostrarAlerta("warning", "La cantidad no debe ser 0.");
    return;
  }

  var formData = new FormData();
  formData.append("action", "agregar-materialE");
  formData.append("id_material", idMaterial);
  formData.append("id_comite", idComite);
  formData.append("cantidad", cantidad);

  $.ajax({
    url: "/funciones/aspectos_func", // Cambia al path correcto
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (resp) {
      if (resp.status === "ok") {
        mostrarAlerta("success", resp.message);
        $("#select-material").val("");
        $("#cantidad").val("");
        tablaMaterial.ajax.reload(); // Si usas DataTables
      } else {
        mostrarAlerta("error", resp.message);
      }
    },
    error: function (xhr, status, error) {
      mostrarAlerta("error", "Error en la petición AJAX: " + error);
    },
  });
});

$("#material-difusion-entregado").on("click", ".delete-material", function (e) {
  e.preventDefault();
  const materialId = $(this).data("id");

  if (!materialId) {
    mostrarAlerta("error", "ID de material no encontrado");
    return;
  }

  if (
    !confirm("¿Seguro que desea remover este material de difusion entregado?")
  ) {
    return;
  }

  $.ajax({
    url: "/funciones/aspectos_func", // Ajusta ruta correcta
    type: "POST",
    dataType: "json",
    data: {
      action: "eliminar-materialE",
      id_material_entregado: materialId,
    },
    success: function (response) {
      if (response.status === "ok") {
        mostrarAlerta("success", response.message);
        tablaMaterial.ajax.reload();
      } else {
        mostrarAlerta("error", response.message);
      }
    },
    error: function () {
      mostrarAlerta("error", "Error al realizar la petición de eliminación");
    },
  });
});

$("#material-difusion-entregado").on("click", ".modal-editar", function (e) {
  e.preventDefault(); // ¡Esto evita el submit del form!
  var id = $(this).data("id");
  var cantidad = $(this).data("cantidad");
  var material = $(this).data("material");
  $("#id_me").val(id);
  $("#descripcion_me").val(material);
  $("#edit_cantidad").val(cantidad);
  $("#editar-material").modal("show");
});

$("#btn-editar-material").on("click", function (e) {
  e.preventDefault();
  var materialId = $("#id_me").val();
  var cantidad = $("#edit_cantidad").val();

  if (!cantidad || cantidad <= 0) {
    mostrarAlerta("warning", "La cantidad no debe ser 0.");
    return;
  }

  $.ajax({
    url: "/funciones/aspectos_func",
    type: "POST",
    dataType: "json",
    data: {
      action: "editar-materialE",
      id_material_entregado: materialId,
      cantidad: cantidad,
    },
    success: function (resp) {
      if (resp.status === "ok") {
        mostrarAlerta("success", resp.message);
        $("#editar-material").modal("hide");
        tablaMaterial.ajax.reload();
      } else {
        mostrarAlerta("error", resp.message);
      }
    },
    error: function () {
      mostrarAlerta("success", "Error al realizar la actualización.");
    },
  });
});
