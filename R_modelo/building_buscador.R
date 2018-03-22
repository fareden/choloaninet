# Loading libraries
library(LSAfun)
#library(magrittr)
#library(lsa)
#library(NLP)
#library(tm)
library(RMySQL)
setwd("~/R_modelo")
#sw<-stopwords(kind="spanish")

#solicitudes<-textmatrix("solicitudes",stopwords=sw)
#w_solicitudes<-lw_tf(solicitudes)
#latent_solicitudes<-lsa(w_solicitudes)

#ofertas<-textmatrix("ofertas",stopwords=sw)
#w_ofertas<-lw_tf(ofertas)
#latent_ofertas<-lsa(w_ofertas)

#documentos<-list.files("todo/",full.names=TRUE)
#set.seed(3141)
#ind<-sample(1:length(documentos),1000)
#docs<-documentos[ind]
#file.copy(docs,"subsample")

#documentos<-list.files("subsample/")
#for(i in 1:length(documentos)){
#	path<-paste0("subsample/",documentos[i])
#	doc<-readLines(path)
#	total_caracteres<-nchar(doc)
#	if(length(total_caracteres)==0){ file.remove(path);next}
#	if(total_caracteres<=30) file.remove(path)
#}
#todo<-textmatrix("subsample",stopwords=sw)
#w_todo<-lw_tf(todo)
#latent_todo<-lsa(w_todo)
#save(latent_todo,file="matriz_1001.RData")
# Query
args <- commandArgs(trailingOnly = TRUE)
busqueda<-args[1]
#busqueda<-"ingeniero agronomo"
print(busqueda)
# Connecting to database to download offers
mydb<-dbConnect(MySQL(), user='rafa', password='choloani', dbname='migrantech', host='lab.achichincle.net',por=3714)
rs<-dbSendQuery(mydb, "select * from oferta")
#Sys.sleep(10)
data<-fetch(rs, n=-1)
dbRemoveTable(mydb,"resultados")
#Building document dir for saving offers as text
data$requisito
for(i in 1:nrow(data)){
	path<-paste0("ofertas_db/",data$id[i])
	texto<-as.character(data$requisito[i])
	writeLines(texto,path)
}
# Loading textmatrix
load("matriz_1001.RData")
matriz<-latent_todo
palabras<-matriz$tk
documentos<-matriz$dk
# Finding related terms to querie
proyec<-neighbors(x=busqueda,400,tvectors=palabras,breakdown=TRUE)
palabras_clave<-names(proyec)
# Finding related offers to querie
ofertas<-list.files("ofertas_db/")
LISTA<-list()
for(i in 1:length(ofertas)){
#for(i in 1:10){
	OFERTA<-readLines(paste0("ofertas_db/",ofertas[i]))
	lista_scores<-list()
	for(j in 1:length(palabras_clave)){
		ind<-grep(pattern=palabras_clave[j],OFERTA,ignore.case=TRUE)
		if(length(ind)!=0) score_palabra = 1*proyec[j] else score_palabra = 0
		lista_scores[[j]]<-score_palabra
	}
	vector_scores<-unlist(lista_scores)
#	promedio<-mean(vector_scores)#El promedio puede ser una medida de calidad
	suma<-sum(vector_scores)
	LISTA[[i]]<-suma
}
names(LISTA)<-ofertas
coincidencias<-unlist(LISTA)
scores<-sort(coincidencias,decreasing=TRUE)
df_scores<-as.data.frame(scores)
df_scores$id<-rownames(df_scores)
rownames(df_scores)<-NULL
write.csv(df_scores,"df_scores1.csv",row.names=FALSE)
dbWriteTable(mydb, name="resultados", value=df_scores)
