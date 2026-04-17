CREATE DEFINER=`root`@`localhost` PROCEDURE `form`.`sp_obtener_registros_matriz`(
    IN p_codigo_pre VARCHAR(50),
    IN p_codigo_sismed VARCHAR(50),
    IN p_fecha_referencia DATE
)
BEGIN
    DECLARE v_fecha_ref DATE;
    DECLARE v_anhio INT;
    DECLARE v_mes INT;
    SET v_fecha_ref = p_fecha_referencia;
    SET v_anhio = YEAR(v_fecha_ref);
    SET v_mes = MONTH(v_fecha_ref);
    
    -- Paso 1: Crear tabla temporal con los datos base filtrados
    CREATE TEMPORARY TABLE IF NOT EXISTS temp_form_det_filtrado AS
    SELECT
        LEFT(CODIGO_PRE, 5) as CODIGO_PRE,
        CODIGO_MED,
        FECHA,
        ANNOMES,
        CREDHOSP,
        DEFNAC,
        EXO,
        INTERSAN,
        OTR_CONV,
        SIS,
        SOAT,
        VENTA,
        OTRAS_SAL,
        STOCK_FIN,
        PRECIO,
        INGRE,
        FEC_EXP,
        TIPSUM,
        ROW_NUMBER() OVER (
            PARTITION BY CODIGO_PRE, CODIGO_MED
            ORDER BY FECHA DESC, STOCK_FIN ASC
        ) as rn_stock
    FROM form_det
    WHERE FECHA >= DATE_SUB(v_fecha_ref, INTERVAL 12 MONTH)
        AND  LEFT(CODIGO_PRE, 5) = p_codigo_pre
        AND (p_codigo_sismed IS NULL OR CODIGO_MED = p_codigo_sismed);
    -- Paso 1.5: Crear tabla temporal con las salidas agregadas por CODIGO_PRE + CODIGO_MED
    CREATE TEMPORARY TABLE IF NOT EXISTS temp_salidas AS
    SELECT 
        CONCAT(cod_ipress, medcod) as codigo_unico,
        cod_ipress,
        medcod,
        SUM(movcantid) as total_salidas
    FROM vw_distribution_salidas
    WHERE Anhio = v_anhio 
      AND MesNumero = v_mes
    GROUP BY cod_ipress, medcod;

    -- Paso 1.6: Crear tabla temporal con las salidas del mes siguiente
    CREATE TEMPORARY TABLE IF NOT EXISTS temp_salidas_mes_siguiente AS
    SELECT 
        CONCAT(cod_ipress, medcod) as codigo_unico,
        cod_ipress,
        medcod,
        SUM(movcantid) as total_salidas
    FROM vw_distribution_salidas
    WHERE Anhio = CASE 
                WHEN v_mes = 12 THEN v_anhio + 1
                ELSE v_anhio
            END
        AND MesNumero = CASE 
                            WHEN v_mes = 12 THEN 1
                            ELSE v_mes + 1
                        END
    GROUP BY cod_ipress, medcod;
    
    -- Paso 2: Consulta base que calcula todas las metricas
    CREATE TEMPORARY TABLE IF NOT EXISTS temp_metricas_base AS
    SELECT
        almacenes.id,
        productos.id as producto_id,
        ANY_VALUE(almacenes.disa_diresa) as disa_diresa,
        ANY_VALUE(almacenes.ue_mef) as ue_mef,
        ANY_VALUE(almacenes.almacen_pertenece) as almacen_pertenece,
        ANY_VALUE(almacenes.red) as red,
        ANY_VALUE(almacenes.microred) as microred,
        ANY_VALUE(almacenes.distrito) as distrito,
        almacenes.cod_ipress,
        ANY_VALUE(almacenes.nombre_ipress) as nombre_ipress,
        ANY_VALUE(almacenes.tipo_establecimiento) as tipo_establecimiento,
        ANY_VALUE(almacenes.ipress_dengue) as ipress_dengue,
        ANY_VALUE(almacenes.nivel) as nivel,
        ANY_VALUE(almacenes.universo_ipress) as universo_ipress,
        productos.cod_sismed,
        ANY_VALUE(productos.cod_unificado) as cod_unificado,
        ANY_VALUE(productos.tipo_prod) as tipo_prod,
        ANY_VALUE(productos.tipo_abastecimiento) as tipo_abastecimiento,
        ANY_VALUE(productos.peti2023) as peti2023,
        ANY_VALUE(productos.estado) as estado,
        ANY_VALUE(productos.producto_fed_actual) as producto_fed_actual,
        ANY_VALUE(productos.producto_cap_eca) as producto_cap_eca,
        ANY_VALUE(productos.iras) as iras,
        ANY_VALUE(productos.dengue) as dengue,
        ANY_VALUE(productos.dengue_grupo_a) as dengue_grupo_a,
        ANY_VALUE(productos.dengue_grupo_b) as dengue_grupo_b,
        ANY_VALUE(productos.dengue_grupo_c) as dengue_grupo_c,
        ANY_VALUE(productos.lista_1) as lista_1,
        ANY_VALUE(productos.lista_2) as lista_2,
        ANY_VALUE(productos.descripcion_cubo) as descripcion_cubo,
        ANY_VALUE(productos.descripcion_producto) as descripcion_producto,
        ANY_VALUE(productos.descripcion_producto_alt) as descripcion_producto_alt,
        -- Consumo por meses (Mes1 a Mes12)
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes1,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes2,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes3,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes4,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes5,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes6,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes7,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes8,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes9,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes10,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes11,
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') 
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as Mes12,
        -- Stock Final
        SUM(CASE WHEN f.ANNOMES = DATE_FORMAT(v_fecha_ref, '%Y%m') THEN f.STOCK_FIN ELSE 0 END) as StockFinal,
        -- Ingresos
        SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN f.INGRE ELSE 0 END) as ingre,
        ANY_VALUE(f.FEC_EXP) as fec_exp,
        -- Consumo Total
        SUM(f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) as consumo_total,
        -- CPMA (Consumo Promedio Mensual Ajustado)
        CASE
            WHEN (
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END)
            ) > 0
            THEN ROUND(
                SUM(f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) /
                (
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END)
                )
            )
            ELSE 0
        END as cpma,
        -- Consumo ultimos 4 meses
        SUM(CASE WHEN DATE_FORMAT(f.FECHA, '%Y%m') BETWEEN DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') AND DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m')
            THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) as consumo_ultimos_4meses,
        -- Meses de Provision (meses_prov)
        CASE
            WHEN (
                CASE
                    WHEN (
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END)
                    ) > 0
                    THEN ROUND(
                        SUM(f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) /
                        (
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END)
                        )
                    )
                    ELSE 0
                END
            ) > 0
            THEN ROUND(
                SUM(CASE WHEN f.ANNOMES = DATE_FORMAT(v_fecha_ref, '%Y%m') THEN f.STOCK_FIN ELSE 0 END) /
                (
                    CASE
                        WHEN (
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END)
                        ) > 0
                        THEN SUM(f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) /
                        (
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                            (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END)
                        )
                        ELSE 0
                    END
                ),
                2
            )
            ELSE 0
        END as meses_prov,
        -- Precio y Monto
        ROUND(SUM(CASE WHEN f.ANNOMES = DATE_FORMAT(v_fecha_ref, '%Y%m') THEN f.PRECIO ELSE 0 END), 2) as precio,
        ROUND(SUM(CASE WHEN f.ANNOMES = DATE_FORMAT(v_fecha_ref, '%Y%m') THEN f.STOCK_FIN ELSE 0 END) * 
              SUM(CASE WHEN f.ANNOMES = DATE_FORMAT(v_fecha_ref, '%Y%m') THEN f.PRECIO ELSE 0 END), 2) as monto,
        -- Meses para vencimiento
        TIMESTAMPDIFF(MONTH, CURDATE(), ANY_VALUE(f.FEC_EXP)) as meses_para_vencimiento,
        -- Situacion fecha vencimiento
        CASE
            WHEN MAX(CASE WHEN f.rn_stock = 1 THEN f.STOCK_FIN END) > 0 THEN
                CASE
                    WHEN TIMESTAMPDIFF(MONTH, v_fecha_ref, MAX(CASE WHEN f.rn_stock = 1 THEN f.FEC_EXP END)) <= 0 THEN 'VENCIDO'
                    WHEN TIMESTAMPDIFF(MONTH, v_fecha_ref, MAX(CASE WHEN f.rn_stock = 1 THEN f.FEC_EXP END)) > 6 THEN ''
                    ELSE 'POR VENCER'
                END
            ELSE ''
        END as sit_fecha_vcmto,
        -- Nueva columna dist1: Suma de salidas del mes actual desde la vista
        COALESCE(s.total_salidas, 0) as dist1,
        COALESCE(s2.total_salidas, 0) as dist2,
        -- Pendiente ICI (versión simplificada)
        GREATEST(COALESCE(s.total_salidas, 0) - SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN f.INGRE ELSE 0 END), 0) as pendingre_ici,
        -- Stock Final Proyectado
        GREATEST(
            SUM(CASE WHEN f.ANNOMES = DATE_FORMAT(v_fecha_ref, '%Y%m') THEN f.STOCK_FIN ELSE 0 END) + 
            GREATEST(COALESCE(s.total_salidas, 0) - SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN f.INGRE ELSE 0 END), 0) + 
            COALESCE(s2.total_salidas, 0) - 
            CASE
                WHEN (
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END)
                ) > 0
                THEN ROUND(
                    SUM(f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) /
                    (
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                        (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END)
                    )
                )
                ELSE 0
            END, 
            0
        ) as stockfinal_proyectado,
                -- Consumo Total Proyectado (suma de los últimos 11 meses + cpma)
        (
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) +
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) +
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) +
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) +
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) +
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) +
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) +
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) +
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) +
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) +
            SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') 
                THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END)
        ) + 
        CASE
            WHEN (
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END)
            ) > 0
            THEN ROUND(
                SUM(f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) /
                (
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 10 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 9 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 8 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 7 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 6 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 5 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 4 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 3 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 2 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL 0 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
                    (CASE WHEN SUM(CASE WHEN DATE_FORMAT(f.FECHA,'%Y%m') = DATE_FORMAT(v_fecha_ref - INTERVAL -1 MONTH,'%Y%m') THEN (f.CREDHOSP + f.DEFNAC + f.EXO + f.INTERSAN + f.OTR_CONV + f.SIS + f.SOAT + f.VENTA + f.OTRAS_SAL) ELSE 0 END) > 0 THEN 1 ELSE 0 END)
                )
            )
            ELSE 0
        END as consumo_total_proyectado,
        f.TIPSUM as TIPSUM
    FROM almacenes
    INNER JOIN temp_form_det_filtrado f ON almacenes.cod_ipress = f.CODIGO_PRE
    INNER JOIN productos ON productos.cod_sismed = f.CODIGO_MED
    LEFT JOIN temp_salidas s ON CONCAT(almacenes.cod_ipress, productos.cod_sismed) = s.codigo_unico
    LEFT JOIN temp_salidas_mes_siguiente s2 ON CONCAT(almacenes.cod_ipress, productos.cod_sismed) = s2.codigo_unico
    WHERE productos.estado = 'C'
    GROUP BY almacenes.id, almacenes.cod_ipress, productos.cod_sismed, productos.id, s.total_salidas,s2.total_salidas,f.TIPSUM;
    
    -- Paso 3: Consulta FINAL que agrega situacion_stock
    SELECT 
        base.*,
        CASE
            WHEN base.StockFinal < 0 THEN 'SALDO NEGATIVO'
            WHEN base.lista_1 = 'Gran Volumen' AND base.meses_prov BETWEEN 1 AND 6 THEN 'NORMOSTOCK'
            WHEN base.StockFinal > 0 AND base.cpma > 0 AND base.meses_prov > 6 THEN 'SOBRESTOCK'
            WHEN base.StockFinal > 0 AND base.cpma = 0 AND base.meses_prov = 0 THEN 'SIN ROTACION'
            WHEN base.StockFinal > 0 AND base.cpma > 0 AND base.meses_prov BETWEEN 2 AND 6 THEN 'NORMOSTOCK'
            WHEN base.StockFinal > 0 AND base.cpma > 0 AND base.meses_prov > 0 AND base.meses_prov < 2 THEN 'SUBSTOCK'
            WHEN base.StockFinal = 0 AND base.cpma > 0 AND base.meses_prov = 0 AND base.consumo_ultimos_4meses > 0 THEN 'DESABASTECIDO'
            WHEN base.StockFinal = 0 AND base.cpma > 0 AND base.meses_prov = 0 AND base.consumo_ultimos_4meses = 0 THEN 'SIN CONSUMO'
            WHEN base.StockFinal = 0 AND base.cpma = 0 AND base.meses_prov = 0 AND base.consumo_ultimos_4meses = 0 THEN 'SIN DATOS'
            ELSE 'SIN CLASIFICAR'
        END as situacion_stock
    FROM temp_metricas_base as base
    ORDER BY base.id DESC;
    
    -- Limpieza de tablas temporales
    DROP TEMPORARY TABLE IF EXISTS temp_form_det_filtrado;
    DROP TEMPORARY TABLE IF EXISTS temp_salidas;
    DROP TEMPORARY TABLE IF EXISTS temp_metricas_base;
    
END;