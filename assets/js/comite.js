$(document).ready(function () {
  // Se inicializa los selectize
  var municipioSelect = $("#select-municipio").selectize({
    create: false,
    sortField: "text",
    placeholder: "Selecciona un municipio...",
  })[0].selectize;

  var coloniaSelect = $("#select-colonia").selectize({
    create: false,
    sortField: "text",
    placeholder: "Selecciona una colonia...",
    disabled: true,
  })[0].selectize;

  var programaSelect = $("#select-programa").selectize({
    create: false,
    sortField: "text",
    placeholder: "Selecciona un programa...",
  })[0].selectize;

  //Código para el cargado de las colonias preseleccionados utilizando selectiza
  var cargandoColonias = false;

  function cargarColonias(id_municipio, id_colonia_preseleccionada) {
    cargandoColonias = true;
    coloniaSelect.clear(true);
    coloniaSelect.clearOptions();
    coloniaSelect.disable();

    if (!id_municipio) {
      coloniaSelect.addOption({
        value: "",
        text: "Selecciona primero un municipio",
      });
      coloniaSelect.setValue("");
      cargandoColonias = false;
      return;
    }

    coloniaSelect.addOption({
      value: "",
      text: "Cargando...",
    });

    $.ajax({
      url: "/funciones/ubicaciones_func",
      type: "POST",
      dataType: "json",
      data: {
        action: "get_colonias",
        id_municipio: id_municipio,
      },
      success: function (data) {
        coloniaSelect.clearOptions();
        coloniaSelect.addOption({
          value: "",
          text: "Selecciona una colonia",
        });

        if (data.length > 0) {
          $.each(data, function (i, row) {
            coloniaSelect.addOption({
              value: row.id_colonia,
              text: row.nombre,
            });
          });
          coloniaSelect.enable();

          if (
            id_colonia_preseleccionada &&
            coloniaSelect.options.hasOwnProperty(id_colonia_preseleccionada)
          ) {
            setTimeout(function () {
              coloniaSelect.setValue(id_colonia_preseleccionada);
            }, 200);
          } else {
            coloniaSelect.setValue("");
          }
        } else {
          coloniaSelect.addOption({
            value: "",
            text: "Sin colonias",
          });
          coloniaSelect.disable();
          coloniaSelect.setValue("");
        }
        cargandoColonias = false;
      },
      error: function () {
        coloniaSelect.clearOptions();
        coloniaSelect.addOption({
          value: "",
          text: "Error al cargar colonias",
        });
        coloniaSelect.disable();
        coloniaSelect.setValue("");
        cargandoColonias = false;
      },
      complete: function () {
        ocultarLoading();
      },
    });
  }

  coloniaSelect.on("change", function (value) {
    if (cargandoColonias) return; // evita conflictos
    // si necesitas lógica adicional, va aquí
  });

  municipioSelect.on("change", function (value) {
    cargarColonias(value, "");
  });

  programaSelect.on("change", function (value) {
    if (!value) {
      $("#input-ramo").val("");
      $("#input-ramo-id").val("");
      $("#input-instancia").val("");
      $("#input-instancia-id").val("");
      return;
    }

    $.ajax({
      url: "/funciones/comites_func",
      type: "POST",
      dataType: "json",
      data: {
        id_programa: value,
        action: "get-programa",
      },
      success: function (response) {
        if (response.status === "ok") {
          var d = response.data;
          var ramo = d.ramo + " - " + d.descripcion;
          $("#input-ramo").val(ramo || "");
          $("#input-instancia").val(d.nombre_instancia || "");
          $("#input-ramo-id").val(d.id_ramo || "");
          $("#input-instancia-id").val(d.id_instancia_ejecutora || "");
        } else {
          $(
            "#input-ramo, #input-ramo-id, #input-instancia, #input-instancia-id"
          ).val("");
          mostrarAlerta("success", response.message);
        }
      },
      error: function () {
        $(
          "#input-ramo, #input-ramo-id, #input-instancia, #input-instancia-id"
        ).val("");

        mostrarAlerta("error", "Error al cargar información del programa");
      },
    });
  });

  // --- NUEVO: Cargar tipos de acción vía AJAX y actualizar select-tipo_accion ---

  var tipoAccionSelect = $("#select-tipo_accion");

  function cargarTiposAccion(idAmbito, tipoAccionSeleccionado) {
    tipoAccionSelect.empty();
    tipoAccionSelect.append(
      '<option value="">Cargando tipos de acción...</option>'
    );

    if (!idAmbito) {
      tipoAccionSelect.empty();
      tipoAccionSelect.append(
        '<option value="">Seleccione primero un ámbito</option>'
      );
      tipoAccionSelect.prop("disabled", true);
      return;
    }

    $.ajax({
      url: "/funciones/comites_func",
      type: "POST",
      dataType: "json",
      data: {
        action: "get-tipos",
        id_ambito: idAmbito,
      },
      success: function (resp) {
        tipoAccionSelect.empty();
        if (resp.status === "ok" && resp.data.length > 0) {
          tipoAccionSelect.append(
            '<option value="">Selecciona un tipo de acción</option>'
          );
          $.each(resp.data, function (i, tipo) {
            tipoAccionSelect.append(
              $("<option>", {
                value: tipo.id_accion,
                text: tipo.descripcion,
              })
            );
          });
          tipoAccionSelect.prop("disabled", false);

          if (tipoAccionSeleccionado) {
            tipoAccionSelect.val(tipoAccionSeleccionado);
          }
        } else {
          tipoAccionSelect.append(
            '<option value="">No hay tipos disponibles</option>'
          );
          tipoAccionSelect.prop("disabled", true);
        }
      },
      error: function () {
        tipoAccionSelect.empty();
        tipoAccionSelect.append(
          '<option value="">Error cargando tipos</option>'
        );
        tipoAccionSelect.prop("disabled", true);
      },
    });
  }

  $("#select-ambito").on("change", function () {
    var idAmbito = $(this).val();
    cargarTiposAccion(idAmbito, "");
  });

  // --- Carga datos del comité para edición ---

  mostrarLoading();
  $.ajax({
    url: "/funciones/comites_func",
    type: "POST",
    data: {
      action: "ver-comite",
      id_comite: id_comite,
    },
    dataType: "json",
    success: function (resp) {
      if (resp.status === "ok") {
        var c = resp.data;
        var id_colonia_preseleccionada = c.fk_id_colonia;
        municipioSelect.setValue(c.fk_id_municipio);
        cargarColonias(c.fk_id_municipio, id_colonia_preseleccionada);
        programaSelect.setValue(c.fk_id_programa);

        // Completar campos de rama e instancia:
        $("#input-ramo").val(c.ramo_descripcion || "");
        $("#input-ramo-id").val(c.fk_id_ramo || "");
        $("#input-instancia").val(c.nombre_instancia || "");
        $("#input-instancia-id").val(c.fk_id_instancia_ejecutora || "");

        $("#folio").text(c.folio);
        $("#fecha").val(c.fecha ? c.fecha.substr(0, 10) : "");
        $("#descripcion").val(c.descripcion || "");
        $("#comentarios").val(c.comentarios || "");
        $("#inversionFormat").val(c.monto_inversion || "");
        $("#inversion").val(c.monto_inversion || "");
        $("#inversionFormat").trigger("keyup");
        $("#proveedor").val(c.proveedor || "");
        $('select[name="constituido"]').val(c.constituido);
        var id_colonia_seleccionada = c.fk_colonia;
        cargarTiposAccion(c.fk_id_ambito, c.fk_id_accion);
        $("#select-ambito").val(c.fk_id_ambito);
        $("#select-reunion").val(c.fk_id_tipo_reunion);
        $("#select-modalidad").val(c.modalidad);
        $("#horario").val(c.horario || "");
        $("#integrantes").val(c.integrantes || "");
        $("#integrantes_m").val(c.integrantes_m || "");
        $("#integrantes_h").val(c.integrantes_h || "");
        $("#beneficiarios").val(c.beneficiarios || "");
        $("#beneficiarios_m").val(c.beneficiarios_m || "");
        $("#beneficiarios_h").val(c.beneficiarios_h || "");

        ocultarLoading();
      } else {
        mostrarAlerta("error", "No se encontró el comité");
        window.location.href = "/comites";
      }
    },
    error: function () {
      ocultarLoading();
      mostrarAlerta("error", "Error al cargar datos del comité");
    },
  });

  $("#form-comite").on("submit", function (e) {
    e.preventDefault();

    // Muestra pantalla de carga (si tienes)
    mostrarLoading();

    const formElement = this;
    var formData = new FormData(formElement);

    // Recolecta todos los datos del formulario
    var formData = {
      action: "editar-comite",
      id_comite: id_comite,
      id_reunion_comite: id_reunion_comite,
      id_municipio: $("#select-municipio").val(),
      id_colonia: $("#select-colonia").val(),
      fecha: $("#fecha").val(),
      id_programa: $("#select-programa").val(),
      id_ambito: $("#select-ambito").val(),
      tipo_accion: $("#select-tipo_accion").val(),
      inversion: $("#inversionFormat").val().replace(/[^0-9.]/g, ""),
      proveedor: $("#proveedor").val(),
      constituido: $('select[name="constituido"]').val(),
      descripcion: $("#descripcion").val(),
      comentarios: $("#comentarios").val(),
      id_tipo_reunion: $('select[name="id_tipo_reunion"]').val(),
      modalidad: $('select[name="modalidad"]').val(),
      horario: $("#horario").val(),
      integrantes: $("#integrantes").val(),
      integrantes_m: $("#integrantes_m").val(),
      integrantes_h: $("#integrantes_h").val(),
      beneficiarios: $("#beneficiarios").val(),
      beneficiarios_m: $("#beneficiarios_m").val(),
      beneficiarios_h: $("#beneficiarios_h").val(),
    };

    $.ajax({
      url: "/funciones/comites_func",
      type: "POST",
      dataType: "json",
      data: formData,
      traditional: true,
      success: function (resp) {
        ocultarLoading();
        if (resp.status === "ok") {
          if (revisarAlGuardar) {
            revisarAlGuardar = false;
            cambiarStatus(id_comite, 1);
          } else {
            mostrarAlerta("success", resp.message);
          }
        } else {
          revisarAlGuardar = false;
          mostrarAlerta("error", "Error: " + resp.message);
        }
      },
      error: function (xhr, status, error) {
        ocultarLoading();
        mostrarAlerta("error", "Error en la petición AJAX: " + error);
      },
    });
  });

  function cambiarStatus(id_comite, status) {
    mostrarLoading();
    $.ajax({
      url: "/funciones/comites_func",
      type: "POST",
      dataType: "json",
      data: {
        action: "cambiar-status",
        id_comite: id_comite,
        status: status,
      },
      success: function (resp) {
        ocultarLoading();
        if (resp.status === "ok") {
          mostrarAlerta("success", resp.message);
          window.location.reload();
        } else {
          mostrarAlerta("error", resp.message);
        }
      },
      error: function (xhr, status, error) {
        ocultarLoading();
        mostrarAlerta("error", "Error: " + error);
      },
    });
  }

  var revisarAlGuardar = false;

  function faltantesRevisionComite() {
    var faltantes = [];
    if (!$("#fecha").val()) {
      faltantes.push("la fecha");
    }
    if (!$("#select-reunion").val()) {
      faltantes.push("el tipo de reunión");
    }
    if (!$("#select-modalidad").val()) {
      faltantes.push("la modalidad");
    }
    if (!$("#horario").val()) {
      faltantes.push("el horario");
    }
    return faltantes;
  }

  function mensajeFaltantesComite(faltantes) {
    var ultimo = faltantes[faltantes.length - 1];
    var lista = faltantes.length === 1 ? ultimo : faltantes.slice(0, -1).join(", ") + " y " + ultimo;
    return "No se puede mandar a revisión. Falta " + lista + ".";
  }

  $("#btnRevision").on("click", function (e) {
    e.preventDefault();
    var faltantes = faltantesRevisionComite();
    if (faltantes.length) {
      mostrarAlerta("error", mensajeFaltantesComite(faltantes));
      return;
    }
    Swal.fire({
      title: "¿Deseas mandar a revisión este comité?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, mandar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ff8400",
      cancelButtonColor: "#d33",
    }).then((result) => {
      if (!result.isConfirmed) {
        return;
      }
      if ($("#fecha").prop("disabled")) {
        cambiarStatus(id_comite, 1);
      } else {
        revisarAlGuardar = true;
        $("#form-comite").trigger("submit");
      }
    });
  });

  $("#btnFinalizar").on("click", function (e) {
    e.preventDefault();
    Swal.fire({
      title: "¿Deseas finalizar este comité?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, finalizar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ff8400",
      cancelButtonColor: "#d33",
    }).then((result) => {
      if (result.isConfirmed) {
        cambiarStatus(id_comite, 2);
      }
    });
  });

  $("#btnLiberar").on("click", function (e) {
    e.preventDefault();
    Swal.fire({
      title: "¿Deseas liberar este comité?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, liberar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ff8400",
      cancelButtonColor: "#d33",
    }).then((result) => {
      if (result.isConfirmed) {
        cambiarStatus(id_comite, 0);
      }
    });
  });

  //AGREGAR USUARIOS PERTENCIENTES A LA REUNION
  var columnaVisible = statusComite === 0;
  var tablaUsuariosR = $("#tabla_reunion_usuarios").DataTable({
    dom: "Brtip",
    ajax: {
      url: "/funciones/usuarios_func",
      type: "POST",
      data: {
        action: "listar-reunion-usuarios",
        id_reunion: id_reunion_comite,
      },
    },
    autoWidth: false,
    columnDefs: [
      {
        targets: 0, // Índice de la columna a ocultar (0 = primera columna)
        visible: false, // La oculta en la tabla
        searchable: false, // Opcional: evita que se busque en esa columna
      },
      { width: "30%", targets: 1 },
      { width: "60%", targets: 2 },
      { width: "10%", targets: 3, visible: columnaVisible },
    ],
    paging: false,
    processing: true,
    serverSide: false,
    language: {
      sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
      sLoadingRecords: "<div style='height:150px'></div>",
      zeroRecords: "No se existen usuarios asignados",
    },
  });

  $("#btn-agregar-usuario").on("click", function () {
    var idUsuario = $("#select-usuario").val();
    var idComite = $("#id_comite").val();

    if (!idUsuario) {
      mostrarAlerta("error", "Por favor selecciona un usuario.");
      return;
    }

    var formData = new FormData();
    formData.append("action", "agregar-reunion-usuarios");
    formData.append("id_usuario", idUsuario);
    formData.append("id_comite", idComite);

    $.ajax({
      url: "/funciones/usuarios_func", // Cambia al path correcto
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (resp) {
        if (resp.status === "ok") {
          mostrarAlerta("success", resp.message);
          $("#select-usuario").val("");
          tablaUsuariosR.ajax.reload(); // Si usas DataTables
        } else {
          mostrarAlerta("error", resp.message);
        }
      },
      error: function (xhr, status, error) {
        mostrarAlerta("error", "Error en la petición AJAX: " + error);
      },
    });
  });

  //delete usuario de la reunion

  $("#usuarios-reunion").on("click", ".delete-usuario-reunion", function (e) {
    e.preventDefault();
    const reunionMId = $(this).data("id");

    if (!reunionMId) {
      mostrarAlerta("error", "ID de reunion miembros no encontrado");
      return;
    }

    if (!confirm("¿Seguro que desea remover a este usuario?")) {
      return;
    }

    $.ajax({
      url: "/funciones/usuarios_func", // Ajusta ruta correcta
      type: "POST",
      dataType: "json",
      data: {
        action: "eliminar-reunion-usuario",
        id_reunion_miembros: reunionMId,
      },
      success: function (response) {
        if (response.status === "ok") {
          mostrarAlerta("success", response.message);
          tablaUsuariosR.ajax.reload();
        } else {
          mostrarAlerta("error", response.message);
        }
      },
      error: function () {
        mostrarAlerta(
          "success",
          "Error al realizar la petición de eliminación"
        );
      },
    });
  });

  // SCRIPTS PARA OTORGAR PERMISOS A USUARIOS PARA EDITAR EL COMITE
  var tablaUsuarios = $("#tabla_usuarios").DataTable({
    dom: "Brtip",
    ajax: {
      url: "/funciones/usuarios_func",
      type: "POST",
      data: {
        action: "listar-permiso",
        id_comite: id_comite,
      },
    },
    autoWidth: false,
    columnDefs: [
      {
        targets: 0, // Índice de la columna a ocultar (0 = primera columna)
        visible: false, // La oculta en la tabla
        searchable: false, // Opcional: evita que se busque en esa columna
      },
      { width: "80%", targets: 1 },
      { width: "20%", targets: 2 },
    ],
    paging: false,
    processing: true,
    serverSide: false,
    language: {
      sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
      sLoadingRecords: "<div style='height:150px'></div>",
      zeroRecords: "No se existen usuarios asignados",
    },
  });

  $("#btn-agregar-permiso").on("click", function () {
    var idUsuario = $("#select-usuario-permiso").val();
    var idComite = $("#id_comite").val();

    if (!idUsuario) {
      mostrarAlerta("error", "Por favor selecciona un usuario.");
      return;
    }

    var formData = new FormData();
    formData.append("action", "agregar-permiso");
    formData.append("id_usuario", idUsuario);
    formData.append("id_comite", idComite);

    $.ajax({
      url: "/funciones/usuarios_func", // Cambia al path correcto
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (resp) {
        if (resp.status === "ok") {
          mostrarAlerta("success", resp.message);
          $("#select-usuario-permiso").val("");
          tablaUsuarios.ajax.reload(); // Si usas DataTables
        } else {
          mostrarAlerta("error", resp.message);
        }
      },
      error: function (xhr, status, error) {
        mostrarAlerta("error", "Error en la petición AJAX: " + error);
      },
    });
  });

  $("#usuarios-permiso").on("click", ".delete-permiso", function (e) {
    e.preventDefault();
    const permisoId = $(this).data("id");

    if (!permisoId) {
      mostrarAlerta("error", "ID de permiso no encontrado");
      return;
    }

    if (!confirm("¿Seguro que desea remover a este usuario?")) {
      return;
    }

    $.ajax({
      url: "/funciones/usuarios_func", // Ajusta ruta correcta
      type: "POST",
      dataType: "json",
      data: {
        action: "eliminar-permiso",
        id_permiso: permisoId,
      },
      success: function (response) {
        if (response.status === "ok") {
          mostrarAlerta("success", response.message);
          tablaUsuarios.ajax.reload();
        } else {
          mostrarAlerta("error", response.message);
        }
      },
      error: function () {
        mostrarAlerta("error", "Error al realizar la petición de eliminación");
      },
    });
  });
  
});
